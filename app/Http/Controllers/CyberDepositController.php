<?php

namespace App\Http\Controllers;

use App\Models\CyberPrintRequest;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\Cyber\DocumentPageCounter;
use App\Services\PhoneNumberFormatter;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

/**
 * Dépôt de documents à imprimer par les clients du cyber, depuis leur téléphone (QR code du comptoir).
 */
class CyberDepositController extends Controller
{
    public const TOKEN_SETTING = 'cyber_upload_token';

    public const MAX_FILES = 10;

    public const MAX_FILE_SIZE_KB = 20480;

    public function show(string $token): View
    {
        $tenantId = $this->tenantIdFor($token);

        return view('cyber.deposit', [
            'token' => $token,
            'spaceName' => $this->spaceName($tenantId),
        ]);
    }

    public function store(Request $request, string $token, DocumentPageCounter $pageCounter): RedirectResponse
    {
        $tenantId = $this->tenantIdFor($token);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'files' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'files.*' => ['required', File::types(CyberPrintRequest::ALLOWED_EXTENSIONS)->max(self::MAX_FILE_SIZE_KB)],
            'color' => ['boolean'],
            'duplex' => ['boolean'],
            'copies' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'files.required' => 'Ajoutez au moins un document à imprimer.',
            'files.max' => 'Vous pouvez envoyer au maximum '.self::MAX_FILES.' documents à la fois.',
            'files.*.mimes' => 'Seuls les fichiers PDF, Word et Excel sont acceptés.',
            'files.*.max' => 'Chaque document doit faire moins de 20 Mo.',
            'customer_phone.phone' => PhoneNumberFormatter::invalidMessage(),
        ], ['customer_name' => 'nom', 'customer_phone' => 'de téléphone']);

        $deposit = TenantContext::run($tenantId, fn () => DB::transaction(function () use ($request, $validated, $tenantId, $pageCounter) {
            $deposit = CyberPrintRequest::create([
                'tenant_id' => $tenantId,
                'code' => CyberPrintRequest::newCode($tenantId),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => PhoneNumberFormatter::format($validated['customer_phone'] ?? null),
                'color' => $request->boolean('color'),
                'duplex' => $request->boolean('duplex'),
                'copies' => (int) $validated['copies'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'recu',
            ]);

            foreach ($request->file('files') as $upload) {
                $extension = strtolower($upload->getClientOriginalExtension());
                $path = $upload->storeAs('cyber-depots/'.$tenantId.'/'.now()->format('Y-m'), Str::random(40).'.'.$extension, 'local');
                $count = $pageCounter->count(Storage::disk('local')->path($path), $extension);

                $deposit->files()->create([
                    'original_name' => Str::limit($upload->getClientOriginalName(), 250, ''),
                    'path' => $path,
                    'extension' => $extension,
                    'size' => (int) $upload->getSize(),
                    'pages' => $count['pages'],
                    'pages_estimated' => $count['estimated'],
                    'count_method' => $count['method'],
                ]);
            }

            return $deposit->load('files');
        }));

        return redirect()->route('cyber.deposit.show', $token)->with('deposit', [
            'code' => $deposit->code,
            'copies' => $deposit->copies,
            'total_pages' => $deposit->totalPages(),
            'files' => $deposit->files->map(fn ($file) => [
                'name' => $file->original_name,
                'pages' => $file->pages,
                'estimated' => $file->pages_estimated,
            ])->all(),
        ]);
    }

    /**
     * Espace (tenant) auquel appartient le QR code ; 404 si le code a été régénéré ou n'existe pas.
     */
    private function tenantIdFor(string $token): int
    {
        abort_unless(strlen($token) >= 20, 404);

        $tenantId = Setting::withoutGlobalScopes()->where('key', self::TOKEN_SETTING)->where('value', $token)->value('tenant_id');
        abort_if($tenantId === null, 404);

        return (int) $tenantId;
    }

    private function spaceName(int $tenantId): string
    {
        $companyName = Setting::withoutGlobalScopes()->where('tenant_id', $tenantId)->where('key', 'company_name')->value('value');

        return $companyName ?: (Tenant::find($tenantId)?->name ?? config('app.name', 'IVOSPHERE'));
    }
}
