<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GedController extends Controller
{
    /**
     * Explorateur de documents (composant Livewire ged-explorer).
     */
    public function index(): View
    {
        return view('ged.index');
    }

    /**
     * Coffre-fort : même explorateur, en mode privé.
     */
    public function vault(Request $request): View
    {
        abort_unless(Document::canAccessVault($request->user()), 403, 'Le coffre-fort est réservé à la direction.');

        return view('ged.vault');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:document_folders,id'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'is_vault' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.rand(100, 999);
        $validated['is_vault'] = $request->boolean('is_vault');

        DocumentFolder::create($validated);

        $route = $validated['is_vault'] ? 'ged.vault' : 'ged.index';

        return redirect()->route($route)->with('success', "Dossier « {$validated['name']} » créé avec succès.");
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'exists:document_folders,id'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'is_vault' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'string'],
            'file' => ['nullable', ...Document::uploadRules()],
        ], [
            'file.mimes' => 'Type de fichier non autorisé (PDF, images, Word, Excel, PowerPoint ou ZIP).',
            'file.extensions' => 'Type de fichier non autorisé (PDF, images, Word, Excel, PowerPoint ou ZIP).',
        ]);

        $isVault = $request->boolean('is_vault');
        abort_if($isVault && ! Document::canAccessVault($request->user()), 403, 'Le coffre-fort est réservé à la direction.');
        $fileName = 'document_'.time().'.pdf';
        $filePath = 'documents/'.$fileName;
        $fileSize = 102400; // default 100 KB mock if simulated
        $mimeType = 'application/pdf';

        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $fileName = $uploaded->getClientOriginalName();
            $fileSize = $uploaded->getSize();
            $mimeType = $uploaded->getMimeType();
            $filePath = $uploaded->store($isVault ? 'vault_files' : 'ged_files', 'local');
        }

        $tagsArray = null;
        if (! empty($validated['tags'])) {
            $tagsArray = array_map('trim', explode(',', $validated['tags']));
        }

        Document::create([
            'title' => $validated['title'],
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'folder_id' => $validated['folder_id'] ?? null,
            'domain_id' => $validated['domain_id'] ?? null,
            'user_id' => auth()->id(),
            'tags' => $tagsArray,
            'is_vault' => $isVault,
            'version' => '1.0',
            'status' => 'active',
        ]);

        $route = $isVault ? 'ged.vault' : 'ged.index';

        return redirect()->route($route)->with('success', "Document « {$validated['title']} » archivé avec succès.");
    }

    /**
     * Ouvre le fichier dans le navigateur (PDF, images…) ou le télécharge avec ?download=1.
     */
    public function open(Request $request, Document $document): StreamedResponse
    {
        abort_unless($document->canBeViewedBy($request->user()), 403);

        $disk = Storage::disk($document->storageDisk());
        abort_unless($document->file_path && $disk->exists($document->file_path), 404, 'Ce fichier est introuvable sur le serveur.');

        $document->increment('downloads_count');

        return $request->boolean('download')
            ? $disk->download($document->file_path, $document->file_name)
            : $disk->response($document->file_path, $document->file_name, [
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox",
            ]);
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        abort_unless($document->canBeDeletedBy($request->user()), 403);

        $isVault = $document->is_vault;
        $document->delete();

        $route = $isVault ? 'ged.vault' : 'ged.index';

        return redirect()->route($route)->with('success', 'Document supprimé.');
    }

    /**
     * Ensure standard folders exist across departments & domains.
     */
    protected function ensureDefaultFolders(): void
    {
        if (DocumentFolder::count() === 0) {
            $defaultFolders = [
                ['name' => 'Ressources Humaines (RH)', 'slug' => 'rh', 'color' => '#0066FF', 'is_vault' => false],
                ['name' => 'Finance & Comptabilité', 'slug' => 'finance', 'color' => '#0B0F14', 'is_vault' => false],
                ['name' => 'Devis & Contrats Commerciaux', 'slug' => 'commercial', 'color' => '#0066FF', 'is_vault' => false],
                ['name' => 'IVOSPHERE PRINT', 'slug' => 'print', 'color' => '#0066FF', 'is_vault' => false],
                ['name' => 'IVOSPHERE TECH', 'slug' => 'tech', 'color' => '#0066FF', 'is_vault' => false],
                ['name' => 'IVOSPHERE MEDIA', 'slug' => 'media', 'color' => '#0066FF', 'is_vault' => false],
                ['name' => 'Coffre-Fort Direction', 'slug' => 'vault-direction', 'color' => '#0B0F14', 'is_vault' => true],
                ['name' => 'Bilans & Titres Légaux', 'slug' => 'vault-legal', 'color' => '#0B0F14', 'is_vault' => true],
            ];

            foreach ($defaultFolders as $f) {
                DocumentFolder::create($f);
            }
        }
    }
}
