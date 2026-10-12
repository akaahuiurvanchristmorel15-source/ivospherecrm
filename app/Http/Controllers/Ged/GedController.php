<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\Domain;
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

    public function vault(Request $request): View
    {
        $this->ensureDefaultFolders();

        $documentsQuery = Document::where('is_vault', true)->with(['user', 'domain']);

        if ($request->filled('search')) {
            $term = $request->search;
            $documentsQuery->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")->orWhere('file_name', 'like', "%{$term}%");
            });
        }

        $documents = $documentsQuery->latest()->paginate(10)->withQueryString();
        $vaultFolders = DocumentFolder::where('is_vault', true)->withCount('documents')->get();
        $domains = Domain::all();

        return view('ged.vault', compact('documents', 'vaultFolders', 'domains'));
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
            'file' => ['nullable', 'file', 'max:20480'], // max 20MB
        ]);

        $isVault = $request->boolean('is_vault');
        $fileName = 'document_'.time().'.pdf';
        $filePath = 'documents/'.$fileName;
        $fileSize = 102400; // default 100 KB mock if simulated
        $mimeType = 'application/pdf';

        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $fileName = $uploaded->getClientOriginalName();
            $fileSize = $uploaded->getSize();
            $mimeType = $uploaded->getMimeType();
            $filePath = $uploaded->store('ged_files', 'public');
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
        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404, 'Ce fichier est introuvable sur le serveur.');

        $document->increment('downloads_count');

        return $request->boolean('download')
            ? Storage::disk('public')->download($document->file_path, $document->file_name)
            : Storage::disk('public')->response($document->file_path, $document->file_name);
    }

    public function destroy(Document $document): RedirectResponse
    {
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
