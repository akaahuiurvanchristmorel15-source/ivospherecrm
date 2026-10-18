<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CyberDepositController;
use App\Models\CyberPrintFile;
use App\Models\CyberPrintRequest;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Côté comptoir du cyber : documents déposés par les clients, QR code d'accès au dépôt.
 */
class CyberDepositDeskController extends Controller
{
    /**
     * Rôles autorisés à régénérer le QR code (l'ancien devient inutilisable).
     *
     * @var list<string>
     */
    private const MANAGER_ROLES = ['administrateur', 'responsable', 'responsable_commercial'];

    /**
     * Dépôts en cours et traités du jour (rafraîchis automatiquement par la page du cyber).
     */
    public function index(): JsonResponse
    {
        $deposits = CyberPrintRequest::with('files')
            ->where(fn ($query) => $query->open()->orWhereDate('updated_at', today()))
            ->latest('id')
            ->limit(40)
            ->get();

        return response()->json([
            'deposits' => $deposits->map(fn (CyberPrintRequest $deposit) => [
                'id' => $deposit->id,
                'code' => $deposit->code,
                'customer_name' => $deposit->customer_name,
                'customer_phone' => $deposit->customer_phone,
                'color' => $deposit->color,
                'duplex' => $deposit->duplex,
                'copies' => $deposit->copies,
                'notes' => $deposit->notes,
                'status' => $deposit->status,
                'status_label' => CyberPrintRequest::STATUSES[$deposit->status][0] ?? $deposit->status,
                'received_at' => $deposit->created_at->format('H:i'),
                'received_ago' => $deposit->created_at->locale('fr')->diffForHumans(),
                'pages_per_copy' => $deposit->pagesPerCopy(),
                'total_pages' => $deposit->totalPages(),
                'status_url' => route('commercial.cyber.deposits.status', $deposit),
                'files' => $deposit->files->map(fn (CyberPrintFile $file) => [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'extension' => $file->extension,
                    'size' => $file->size,
                    'pages' => $file->pages,
                    'estimated' => $file->pages_estimated,
                    'download_url' => route('commercial.cyber.deposits.download', [$deposit, $file]),
                ])->values(),
            ])->values(),
        ]);
    }

    public function download(CyberPrintRequest $cyberPrintRequest, CyberPrintFile $cyberPrintFile): StreamedResponse
    {
        abort_unless($cyberPrintFile->cyber_print_request_id === $cyberPrintRequest->id, 404);
        abort_unless(Storage::disk('local')->exists($cyberPrintFile->path), 404);

        // PDF ouvert dans le navigateur pour impression directe ; Word / Excel téléchargés
        return $cyberPrintFile->extension === 'pdf'
            ? Storage::disk('local')->response($cyberPrintFile->path, $cyberPrintFile->original_name, ['Content-Type' => 'application/pdf'], 'inline')
            : Storage::disk('local')->download($cyberPrintFile->path, $cyberPrintFile->original_name);
    }

    public function updateStatus(Request $request, CyberPrintRequest $cyberPrintRequest): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(CyberPrintRequest::STATUSES))],
        ]);

        $cyberPrintRequest->update(['status' => $validated['status'], 'handled_by' => $request->user()->id]);

        ActivityLogger::log(
            action: 'depot_cyber_statut',
            description: "Dépôt d'impression {$cyberPrintRequest->code} : ".CyberPrintRequest::STATUSES[$validated['status']][0],
            subject: $cyberPrintRequest
        );

        return $request->expectsJson()
            ? response()->json(['status' => $cyberPrintRequest->status])
            : back()->with('success', "Dépôt {$cyberPrintRequest->code} mis à jour.");
    }

    /**
     * Affiche du QR code à placer au comptoir.
     */
    public function qr(Request $request, QrCodeService $qrCodeService): View
    {
        $url = route('cyber.deposit.show', self::uploadToken());

        return view('commercial.cyber.qr', [
            'url' => $url,
            'qrSvg' => $qrCodeService->generateSvg($url),
            'companyName' => Setting::get('company_name', config('app.name', 'IVOSPHERE')),
            'canRegenerate' => $request->user()->hasRole(self::MANAGER_ROLES),
        ]);
    }

    public function regenerateQr(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole(self::MANAGER_ROLES), 403);

        Setting::set(CyberDepositController::TOKEN_SETTING, Str::random(40));

        ActivityLogger::log(action: 'depot_cyber_qr', description: 'A régénéré le QR code de dépôt des documents du cyber');

        return redirect()->route('commercial.cyber.qr')->with('success', "Nouveau QR code généré : l'ancien ne fonctionne plus. Imprimez la nouvelle affiche.");
    }

    /**
     * Jeton du lien de dépôt de l'espace courant (créé à la première utilisation).
     */
    public static function uploadToken(): string
    {
        $token = (string) Setting::get(CyberDepositController::TOKEN_SETTING, '');

        if (strlen($token) < 20) {
            $token = Str::random(40);
            Setting::set(CyberDepositController::TOKEN_SETTING, $token);
        }

        return $token;
    }
}
