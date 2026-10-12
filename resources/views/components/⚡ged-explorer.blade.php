<?php

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\Domain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Explorateur de la GED : navigation dans les dossiers, recherche et filtres instantanés,
 * dépôt de fichiers par glisser-déposer, sans rechargement de page.
 */
new class extends Component {
    use WithFileUploads;
    use WithPagination;

    /**
     * Familles de fichiers : [libellé, extensions].
     *
     * @var array<string, array{0: string, 1: list<string>}>
     */
    public const TYPES = [
        'pdf' => ['PDF', ['pdf']],
        'image' => ['Images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']],
        'word' => ['Word', ['doc', 'docx', 'odt', 'rtf', 'txt']],
        'sheet' => ['Excel', ['xls', 'xlsx', 'ods', 'csv']],
    ];

    #[Url(as: 'dossier')]
    public ?int $folderId = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $type = '';

    #[Url(as: 'vue')]
    public string $view = 'grid';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    public ?int $uploadDomainId = null;

    public string $uploadTags = '';

    public string $newFolderName = '';

    public string $newFolderColor = '#0066FF';

    public function mount(): void
    {
        $this->ensureDefaultFolders();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'type', 'folderId'], true)) {
            $this->resetPage();
        }
    }

    public function openFolder(?int $folderId): void
    {
        $this->folderId = $folderId;
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Enregistre les fichiers déposés dans le dossier courant dès la fin de leur envoi.
     */
    public function updatedUploads(): void
    {
        $this->validate([
            'uploads' => ['array', 'max:20'],
            'uploads.*' => ['file', 'max:20480'],
            'uploadDomainId' => ['nullable', 'exists:domains,id'],
            'uploadTags' => ['nullable', 'string', 'max:255'],
        ], [
            'uploads.*.max' => 'Chaque fichier doit faire 20 Mo au maximum.',
            'uploads.max' => 'Déposez 20 fichiers au maximum à la fois.',
        ]);

        $tags = collect(explode(',', $this->uploadTags))->map(fn (string $tag) => trim($tag))->filter()->values()->all();

        foreach ($this->uploads as $upload) {
            $originalName = $upload->getClientOriginalName();

            Document::create([
                'title' => Str::of(pathinfo($originalName, PATHINFO_FILENAME))->replace(['_', '-'], ' ')->squish()->ucfirst()->limit(255, ''),
                'file_name' => $originalName,
                'file_path' => $upload->store('ged_files', 'public'),
                'file_size' => $upload->getSize(),
                'mime_type' => $upload->getMimeType(),
                'folder_id' => $this->folderId,
                'domain_id' => $this->uploadDomainId,
                'user_id' => auth()->id(),
                'tags' => $tags ?: null,
                'is_vault' => false,
                'version' => '1.0',
                'status' => 'active',
            ]);
        }

        $count = count($this->uploads);
        $this->reset('uploads');
        $this->resetPage();
        $this->dispatch('ged-toast', message: $count > 1 ? "{$count} documents archivés." : 'Document archivé.');
    }

    public function createFolder(): void
    {
        $validated = $this->validate([
            'newFolderName' => ['required', 'string', 'max:100'],
            'newFolderColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'newFolderName.required' => 'Donnez un nom au dossier.',
        ]);

        DocumentFolder::create([
            'name' => trim($validated['newFolderName']),
            'slug' => Str::slug($validated['newFolderName']).'-'.Str::lower(Str::random(4)),
            'parent_id' => $this->folderId,
            'color' => $validated['newFolderColor'],
            'is_vault' => false,
        ]);

        $this->reset('newFolderName');
        $this->dispatch('ged-folder-created');
        $this->dispatch('ged-toast', message: 'Dossier créé.');
    }

    public function deleteDocument(int $documentId): void
    {
        $document = Document::where('is_vault', false)->findOrFail($documentId);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->dispatch('ged-toast', message: 'Document supprimé.');
    }

    #[Computed]
    public function currentFolder(): ?DocumentFolder
    {
        return $this->folderId ? DocumentFolder::where('is_vault', false)->find($this->folderId) : null;
    }

    /**
     * Chemin du dossier racine jusqu'au dossier courant.
     *
     * @return Collection<int, DocumentFolder>
     */
    #[Computed]
    public function breadcrumb(): Collection
    {
        $trail = collect();
        $folder = $this->currentFolder;

        while ($folder && $trail->count() < 10) {
            $trail->prepend($folder);
            $folder = $folder->parent;
        }

        return $trail;
    }

    #[Computed]
    public function folders(): Collection
    {
        return DocumentFolder::where('is_vault', false)
            ->where('parent_id', $this->folderId)
            ->withCount(['documents', 'children'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function documents()
    {
        $extensions = self::TYPES[$this->type][1] ?? null;

        return Document::where('is_vault', false)
            ->with(['user', 'domain', 'folder'])
            // Une recherche porte sur toute la GED ; sinon on affiche le dossier courant
            ->when($this->search === '', fn ($query) => $query->where('folder_id', $this->folderId))
            ->when($this->search !== '', fn ($query) => $query->where(function ($inner) {
                $term = '%'.trim($this->search).'%';
                $inner->where('title', 'like', $term)->orWhere('file_name', 'like', $term)->orWhere('tags', 'like', $term);
            }))
            ->when($extensions, fn ($query) => $query->where(function ($inner) use ($extensions) {
                foreach ($extensions as $extension) {
                    $inner->orWhere('file_name', 'like', "%.{$extension}");
                }
            }))
            ->latest()
            ->paginate(24);
    }

    /**
     * @return array{documents: int, size: int, week: int}
     */
    #[Computed]
    public function stats(): array
    {
        $documents = Document::where('is_vault', false);

        return [
            'documents' => (clone $documents)->count(),
            'size' => (int) (clone $documents)->sum('file_size'),
            'week' => (clone $documents)->where('created_at', '>=', now()->subWeek())->count(),
        ];
    }

    public static function familyOf(string $fileName): string
    {
        $extension = Str::lower(pathinfo($fileName, PATHINFO_EXTENSION));

        foreach (self::TYPES as $family => [, $extensions]) {
            if (in_array($extension, $extensions, true)) {
                return $family;
            }
        }

        return 'other';
    }

    public static function humanSize(int $bytes): string
    {
        return match (true) {
            $bytes >= 1073741824 => number_format($bytes / 1073741824, 1, ',', ' ').' Go',
            $bytes >= 1048576 => number_format($bytes / 1048576, 1, ',', ' ').' Mo',
            $bytes >= 1024 => number_format($bytes / 1024, 0, ',', ' ').' Ko',
            default => $bytes.' o',
        };
    }

    public function render()
    {
        return $this->view(['domains' => Domain::orderBy('name')->get()]);
    }

    private function ensureDefaultFolders(): void
    {
        if (DocumentFolder::exists()) {
            return;
        }

        foreach ([
            ['Ressources Humaines (RH)', 'rh', '#0066FF', false],
            ['Finance & Comptabilité', 'finance', '#0B0F14', false],
            ['Devis & Contrats Commerciaux', 'commercial', '#10B981', false],
            ['IVOSPHERE PRINT', 'print', '#0066FF', false],
            ['IVOSPHERE TECH', 'tech', '#6366F1', false],
            ['IVOSPHERE MEDIA', 'media', '#F59E0B', false],
            ['Coffre-Fort Direction', 'vault-direction', '#0B0F14', true],
            ['Bilans & Titres Légaux', 'vault-legal', '#0B0F14', true],
        ] as [$name, $slug, $color, $isVault]) {
            DocumentFolder::create(['name' => $name, 'slug' => $slug, 'color' => $color, 'is_vault' => $isVault]);
        }
    }
};
?>

@php
    $familyStyles = [
        'pdf' => ['PDF', 'bg-rose-50 text-rose-600'],
        'image' => ['IMG', 'bg-violet-50 text-violet-600'],
        'word' => ['DOC', 'bg-blue-50 text-[#0066FF]'],
        'sheet' => ['XLS', 'bg-emerald-50 text-emerald-600'],
        'other' => ['FIC', 'bg-slate-100 text-slate-500'],
    ];
    $folderColors = ['#0066FF', '#10B981', '#F59E0B', '#EC4899', '#6366F1', '#0B0F14'];
@endphp

<div x-data="{
        dragging: false,
        uploading: false,
        progress: 0,
        newFolder: false,
        toast: null,
        toastTimer: null,
        showToast(message) {
            this.toast = message;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => this.toast = null, 2800);
        },
        dropFiles(event) {
            this.dragging = false;
            if (!event.dataTransfer.files.length) return;
            $wire.uploadMultiple('uploads', event.dataTransfer.files,
                () => { this.uploading = false; this.progress = 0; },
                () => { this.uploading = false; },
                (e) => { this.uploading = true; this.progress = e.detail.progress; });
        },
     }"
     x-on:ged-toast.window="showToast($event.detail.message)"
     x-on:ged-folder-created.window="newFolder = false"
     x-on:dragover.prevent="dragging = true"
     x-on:dragleave.self="dragging = false"
     x-on:drop.prevent="dropFiles($event)"
     x-on:livewire-upload-start="uploading = true"
     x-on:livewire-upload-finish="uploading = false; progress = 0"
     x-on:livewire-upload-error="uploading = false"
     x-on:livewire-upload-progress="progress = $event.detail.progress"
     class="relative mx-auto max-w-7xl space-y-5">

    {{-- ═══ En-tête ═══ --}}
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight text-[#0B0F14]">Documents</h1>
            <p class="text-sm text-[#64748B]">
                {{ number_format($this->stats['documents'], 0, ',', ' ') }} document(s) · {{ $this::humanSize($this->stats['size']) }}
                @if($this->stats['week'])
                    · <span class="font-medium text-emerald-600">+{{ $this->stats['week'] }} cette semaine</span>
                @endif
            </p>
        </div>
        <div class="grid grid-cols-3 gap-2 sm:flex">
            <a href="{{ route('ged.vault') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm font-medium text-[#0B0F14] transition hover:bg-[#F5F7FA]">
                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <span class="hidden sm:inline">Coffre-fort</span>
            </a>
            <button type="button" @click="newFolder = !newFolder; $nextTick(() => $refs.folderName?.focus())"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-3.5 text-sm font-medium text-[#0B0F14] transition hover:bg-[#F5F7FA]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                <span class="hidden sm:inline">Dossier</span>
            </button>
            <label class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#0066FF] px-4 text-sm font-semibold text-white transition hover:bg-[#0052CC]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <span>Importer</span>
                <input type="file" multiple wire:model="uploads" class="sr-only">
            </label>
        </div>
    </header>

    {{-- ═══ Nouveau dossier (dans le dossier courant) ═══ --}}
    <form x-show="newFolder" x-cloak x-transition wire:submit="createFolder"
          class="flex flex-col gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4 sm:flex-row sm:items-center">
        <div class="min-w-0 flex-1">
            <input type="text" x-ref="folderName" wire:model="newFolderName" maxlength="100"
                   placeholder="Nom du nouveau dossier{{ $this->currentFolder ? ' dans « '.$this->currentFolder->name.' »' : '' }}"
                   class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-base text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15 sm:text-sm">
            @error('newFolderName') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-1.5" role="radiogroup" aria-label="Couleur du dossier">
            @foreach($folderColors as $color)
                <button type="button" wire:click="$set('newFolderColor', '{{ $color }}')" role="radio" aria-checked="{{ $newFolderColor === $color ? 'true' : 'false' }}" aria-label="Couleur {{ $color }}"
                        class="h-6 w-6 rounded-full transition {{ $newFolderColor === $color ? 'ring-2 ring-offset-2 ring-[#0B0F14]' : 'hover:scale-110' }}" style="background: {{ $color }}"></button>
            @endforeach
        </div>
        <div class="flex gap-2">
            <button type="button" @click="newFolder = false" class="h-10 flex-1 rounded-xl border border-[#E2E8F0] px-4 text-sm font-medium text-slate-600 hover:bg-[#F5F7FA] sm:flex-none">Annuler</button>
            <button type="submit" class="h-10 flex-1 rounded-xl bg-[#0B0F14] px-4 text-sm font-semibold text-white hover:bg-slate-800 sm:flex-none">Créer</button>
        </div>
    </form>

    {{-- ═══ Recherche, types, vue ═══ --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="relative min-w-0 flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94A3B8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher dans toute la GED (titre, fichier, tag)"
                   class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-white pl-10 pr-10 text-base text-[#0B0F14] placeholder:text-[#94A3B8] focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15 sm:text-sm">
            <svg wire:loading wire:target="search" class="absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-[#0066FF]" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20"/><path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        </div>
        <div class="flex items-center gap-2">
            <div class="no-scrollbar -mx-4 flex flex-1 gap-1.5 overflow-x-auto px-4 lg:mx-0 lg:px-0">
                <button type="button" wire:click="$set('type', '')"
                        class="h-9 shrink-0 rounded-full px-3.5 text-xs font-medium transition {{ $type === '' ? 'bg-[#0B0F14] text-white' : 'bg-white text-[#64748B] ring-1 ring-[#E2E8F0] hover:text-[#0B0F14]' }}">Tous</button>
                @foreach($this::TYPES as $key => [$label])
                    <button type="button" wire:click="$set('type', '{{ $key }}')"
                            class="h-9 shrink-0 rounded-full px-3.5 text-xs font-medium transition {{ $type === $key ? 'bg-[#0B0F14] text-white' : 'bg-white text-[#64748B] ring-1 ring-[#E2E8F0] hover:text-[#0B0F14]' }}">{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex shrink-0 rounded-xl bg-[#F1F5F9] p-1" role="group" aria-label="Affichage">
                <button type="button" wire:click="$set('view', 'grid')" aria-label="Grille" aria-pressed="{{ $view === 'grid' ? 'true' : 'false' }}"
                        class="rounded-lg p-1.5 transition {{ $view === 'grid' ? 'bg-white text-[#0B0F14] shadow-sm' : 'text-[#94A3B8] hover:text-[#0B0F14]' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </button>
                <button type="button" wire:click="$set('view', 'list')" aria-label="Liste" aria-pressed="{{ $view === 'list' ? 'true' : 'false' }}"
                        class="rounded-lg p-1.5 transition {{ $view === 'list' ? 'bg-white text-[#0B0F14] shadow-sm' : 'text-[#94A3B8] hover:text-[#0B0F14]' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ Fil d'Ariane ═══ --}}
    @if($search === '')
        <nav aria-label="Emplacement" class="flex flex-wrap items-center gap-1 text-sm">
            <button type="button" wire:click="openFolder(null)" class="rounded-lg px-2 py-1 font-medium transition {{ $this->currentFolder ? 'text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0B0F14]' : 'text-[#0B0F14]' }}">Tous les dossiers</button>
            @foreach($this->breadcrumb as $crumb)
                <svg class="h-4 w-4 text-[#CBD5E1]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <button type="button" wire:click="openFolder({{ $crumb->id }})" wire:key="crumb-{{ $crumb->id }}"
                        class="rounded-lg px-2 py-1 font-medium transition {{ $loop->last ? 'text-[#0B0F14]' : 'text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0B0F14]' }}">{{ $crumb->name }}</button>
            @endforeach
        </nav>
    @else
        <p class="text-sm text-[#64748B]">Résultats pour « <span class="font-medium text-[#0B0F14]">{{ $search }}</span> » dans toute la GED</p>
    @endif

    {{-- ═══ Contenu (atténué pendant le chargement) ═══ --}}
    <div wire:loading.class="opacity-60" wire:target="openFolder,search,type,view,gotoPage,nextPage,previousPage" class="space-y-6 transition-opacity">

        {{-- Dossiers --}}
        @if($search === '' && $this->folders->isNotEmpty())
            <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach($this->folders as $folder)
                    <button type="button" wire:click="openFolder({{ $folder->id }})" wire:key="folder-{{ $folder->id }}"
                            class="group flex items-center gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-3.5 text-left transition duration-200 hover:-translate-y-0.5 hover:border-transparent hover:shadow-lg hover:shadow-slate-200/70">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition duration-200 group-hover:scale-105" style="background: {{ $folder->color ?? '#0066FF' }}1A; color: {{ $folder->color ?? '#0066FF' }}">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.5 21a3 3 0 003-3v-4.5a3 3 0 00-3-3h-15a3 3 0 00-3 3V18a3 3 0 003 3h15zM1.5 10.146V6a3 3 0 013-3h5.379a2.25 2.25 0 011.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 013 3v1.146A4.483 4.483 0 0019.5 9h-15a4.483 4.483 0 00-3 1.146z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-[#0B0F14]">{{ $folder->name }}</span>
                            <span class="block text-xs text-[#94A3B8]">
                                {{ $folder->documents_count }} doc.@if($folder->children_count) · {{ $folder->children_count }} dossier(s)@endif
                            </span>
                        </span>
                    </button>
                @endforeach
            </section>
        @endif

        {{-- Documents --}}
        @php $documents = $this->documents; @endphp

        @if($documents->isEmpty())
            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-[#CBD5E1] bg-white px-6 py-14 text-center transition hover:border-[#0066FF] hover:bg-[#0066FF]/[0.02]">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F1F5F9] text-[#64748B]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                </span>
                <span class="text-sm font-semibold text-[#0B0F14]">{{ $search !== '' || $type !== '' ? 'Aucun document ne correspond' : 'Ce dossier est vide' }}</span>
                <span class="text-xs text-[#64748B]">Glissez vos fichiers ici ou cliquez pour les choisir</span>
                <input type="file" multiple wire:model="uploads" class="sr-only">
            </label>
        @elseif($view === 'grid')
            <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                @foreach($documents as $document)
                    @php [$badge, $badgeClass] = $familyStyles[$this::familyOf($document->file_name)]; @endphp
                    <article wire:key="doc-{{ $document->id }}" class="group relative flex flex-col overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white transition duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/70">
                        <a href="{{ route('ged.documents.open', $document) }}" target="_blank" rel="noopener" class="flex aspect-[4/3] items-center justify-center bg-[#F8FAFC]">
                            @if($this::familyOf($document->file_name) === 'image' && \Illuminate\Support\Facades\Storage::disk('public')->exists((string) $document->file_path))
                                <img src="{{ asset('storage/'.$document->file_path) }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <span class="rounded-xl px-3 py-2 text-sm font-bold tracking-wide transition duration-300 group-hover:scale-110 {{ $badgeClass }}">{{ $badge }}</span>
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col gap-1 p-3">
                            <p class="line-clamp-2 text-sm font-semibold leading-snug text-[#0B0F14]" title="{{ $document->title }}">{{ $document->title }}</p>
                            <p class="text-[11px] text-[#94A3B8]">{{ $this::humanSize((int) $document->file_size) }} · {{ $document->created_at->translatedFormat('d M') }}</p>
                            @if($search !== '' && $document->folder)
                                <p class="truncate text-[11px] text-[#64748B]">{{ $document->folder->name }}</p>
                            @endif
                        </div>
                        <div class="absolute right-2 top-2 flex gap-1 opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100">
                            <a href="{{ route('ged.documents.open', ['document' => $document, 'download' => 1]) }}" aria-label="Télécharger {{ $document->title }}" class="rounded-lg bg-white/95 p-1.5 text-[#64748B] shadow-sm hover:text-[#0066FF]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </a>
                            <button type="button" wire:click="deleteDocument({{ $document->id }})" wire:confirm="Supprimer « {{ $document->title }} » ?" aria-label="Supprimer {{ $document->title }}" class="rounded-lg bg-white/95 p-1.5 text-[#64748B] shadow-sm hover:text-rose-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </button>
                        </div>
                    </article>
                @endforeach
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                <ul class="divide-y divide-[#E2E8F0]">
                    @foreach($documents as $document)
                        @php [$badge, $badgeClass] = $familyStyles[$this::familyOf($document->file_name)]; @endphp
                        <li wire:key="row-{{ $document->id }}" class="group flex items-center gap-3 px-4 py-3 transition hover:bg-[#F8FAFC]">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-[11px] font-bold {{ $badgeClass }}">{{ $badge }}</span>
                            <a href="{{ route('ged.documents.open', $document) }}" target="_blank" rel="noopener" class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-[#0B0F14] group-hover:text-[#0066FF]">{{ $document->title }}</span>
                                <span class="block truncate text-xs text-[#94A3B8]">
                                    {{ $document->file_name }}@if($search !== '' && $document->folder) · {{ $document->folder->name }}@endif
                                </span>
                            </a>
                            <span class="hidden shrink-0 gap-1 md:flex">
                                @foreach(array_slice($document->tags ?? [], 0, 3) as $tag)
                                    <span class="rounded-md bg-[#F1F5F9] px-1.5 py-0.5 text-[11px] text-[#64748B]">#{{ $tag }}</span>
                                @endforeach
                            </span>
                            <span class="hidden w-20 shrink-0 text-right text-xs tabular-nums text-[#64748B] sm:block">{{ $this::humanSize((int) $document->file_size) }}</span>
                            <span class="hidden w-24 shrink-0 text-right text-xs text-[#94A3B8] sm:block">{{ $document->created_at->format('d/m/Y') }}</span>
                            <a href="{{ route('ged.documents.open', ['document' => $document, 'download' => 1]) }}" aria-label="Télécharger {{ $document->title }}" class="shrink-0 rounded-lg p-1.5 text-[#94A3B8] hover:bg-white hover:text-[#0066FF]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            </a>
                            <button type="button" wire:click="deleteDocument({{ $document->id }})" wire:confirm="Supprimer « {{ $document->title }} » ?" aria-label="Supprimer {{ $document->title }}" class="shrink-0 rounded-lg p-1.5 text-[#94A3B8] hover:bg-white hover:text-rose-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($documents->hasPages())
            <div>{{ $documents->links() }}</div>
        @endif
    </div>

    {{-- ═══ Options d'import (pôle, tags) ═══ --}}
    <details class="rounded-2xl border border-[#E2E8F0] bg-white px-4 py-3 text-sm">
        <summary class="cursor-pointer select-none font-medium text-[#64748B]">Options appliquées aux prochains fichiers importés</summary>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-[#64748B]">Pôle</span>
                <select wire:model="uploadDomainId" class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
                    <option value="">Général</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-[#64748B]">Tags (séparés par des virgules)</span>
                <input type="text" wire:model="uploadTags" placeholder="contrat, 2026, client" class="h-10 w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-sm focus:border-[#0066FF] focus:outline-none focus:ring-2 focus:ring-[#0066FF]/15">
            </label>
        </div>
    </details>

    @error('uploads.*') <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</p> @enderror
    @error('uploads') <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</p> @enderror

    {{-- ═══ Zone de dépôt plein écran pendant le glisser-déposer ═══ --}}
    <div x-show="dragging" x-cloak x-transition.opacity
         class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center bg-[#0066FF]/10 p-6 backdrop-blur-sm">
        <div class="rounded-3xl border-2 border-dashed border-[#0066FF] bg-white/95 px-10 py-12 text-center shadow-2xl">
            <svg class="mx-auto h-10 w-10 animate-bounce text-[#0066FF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            <p class="mt-3 text-base font-semibold text-[#0B0F14]">Déposez vos fichiers</p>
            <p class="text-sm text-[#64748B]">dans « {{ $this->currentFolder?->name ?? 'Tous les dossiers' }} »</p>
        </div>
    </div>

    {{-- ═══ Progression de l'envoi ═══ --}}
    <div x-show="uploading" x-cloak x-transition class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-sm rounded-2xl bg-[#0B0F14] p-4 text-white shadow-2xl sm:inset-x-auto sm:right-6">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium">Envoi en cours…</span>
            <span class="tabular-nums" x-text="progress + ' %'"></span>
        </div>
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/15">
            <div class="h-full rounded-full bg-[#0066FF] transition-all duration-200" :style="`width: ${progress}%`"></div>
        </div>
    </div>

    {{-- ═══ Notification ═══ --}}
    <div x-show="toast" x-cloak x-transition.opacity.duration.200ms role="status"
         class="fixed bottom-4 left-1/2 z-50 -translate-x-1/2 rounded-full bg-[#0B0F14] px-4 py-2 text-sm font-medium text-white shadow-xl"
         x-text="toast"></div>
</div>
