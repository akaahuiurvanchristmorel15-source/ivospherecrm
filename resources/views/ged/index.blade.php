<x-layouts.app title="GED — Gestion Électronique des Documents">
    <div class="space-y-6" x-data="{ uploadModal: false, newFolderModal: false }">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#E2E8F0]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] bg-[#0066FF]/10 text-[#0066FF] font-bold uppercase tracking-wider">Archivage Numérique</span>
                    <span class="text-xs text-slate-500">Arborescence par Pôle & Domaine</span>
                </div>
                <h1 class="text-2xl font-bold text-[#0B0F14] tracking-tight mt-1">GED — Gestion Documentaire</h1>
                <p class="text-xs text-slate-500">Centralisation, classement, versioning et indexation des fichiers d'entreprise.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('ged.vault') }}" class="px-3.5 py-2 rounded-xl bg-[#0B0F14] hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Coffre-Fort Numérique 🔒</span>
                </a>

                <button @click="newFolderModal = true" class="px-3.5 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:bg-slate-50 text-xs font-semibold text-[#0B0F14] transition-colors">
                    + Dossier
                </button>

                <button @click="uploadModal = true" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Archiver Document</span>
                </button>
            </div>
        </div>

        <!-- Search & Filter bar -->
        <div class="bg-white rounded-xl p-3 border border-[#E2E8F0] shadow-xs flex items-center justify-between gap-4">
            <form action="{{ route('ged.index') }}" method="GET" class="flex-1 flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Rechercher par titre, nom de fichier ou mot-clé..." 
                    class="w-full px-3 py-1.5 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0] text-xs text-[#0B0F14] focus:ring-1 focus:ring-[#0066FF]"
                />
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0066FF] text-white text-xs font-semibold">
                    Filtrer
                </button>
            </form>
            @if($currentFolder)
                <a href="{{ route('ged.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#0066FF] hover:underline font-medium shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Racine (Tous dossiers)</span>
                </a>
            @endif
        </div>

        <!-- Folders Grid -->
        <div>
            <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider mb-3">Dossiers & Départements</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($folders as $folder)
                    <a 
                        href="{{ route('ged.index', ['folder_id' => $folder->id]) }}"
                        class="p-4 rounded-xl bg-white border border-[#E2E8F0] hover:border-[#0066FF] shadow-xs hover:shadow-sm transition-all group flex items-center gap-3"
                    >
                        <div class="w-10 h-10 rounded-lg bg-[#F5F7FA] text-[#0066FF] flex items-center justify-center shrink-0 group-hover:bg-[#0066FF] group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-xs text-[#0B0F14] truncate group-hover:text-[#0066FF]">{{ $folder->name }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $folder->documents_count }} document(s)</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Documents Table -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <h3 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">
                    {{ $currentFolder ? 'Documents dans ' . $currentFolder->name : 'Tous les Documents Récents' }}
                </h3>
                <span class="text-xs text-slate-400">{{ $documents->total() }} fichier(s)</span>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-[#0B0F14]">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Document</th>
                            <th class="py-3 px-4">Taille & Version</th>
                            <th class="py-3 px-4">Pôle / Catégorie</th>
                            <th class="py-3 px-4">Tags</th>
                            <th class="py-3 px-4">Archivé le</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documents as $doc)
                            <tr class="hover:bg-[#F5F7FA] transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 border border-slate-200">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <span class="font-bold text-[#0B0F14] block">{{ $doc->title }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $doc->file_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-[11px] text-slate-500">
                                    <span>{{ $doc->formatted_size }}</span>
                                    <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 ml-1">v{{ $doc->version }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-[#0066FF]/10 text-[#0066FF]">
                                        {{ $doc->domain ? $doc->domain->name : 'Général' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if(!empty($doc->tags))
                                        <div class="flex gap-1 flex-wrap">
                                            @foreach($doc->tags as $tg)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] bg-slate-100 text-slate-600">#{{ $tg }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[10px]">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[11px] text-slate-400">
                                    {{ $doc->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('ged.documents.destroy', $doc) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer ce document ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-medium">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    Aucun document dans cet emplacement.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Amplified Cards View (< md) -->
            <div class="block md:hidden p-3 sm:p-4 space-y-3">
                @forelse($documents as $doc)
                    <div class="bg-white rounded-xl p-4 border border-[#E2E8F0] shadow-xs hover:border-[#0066FF]/30 transition-all flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                                    <svg class="w-5 h-5 text-[#0066FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-sm text-[#0B0F14] block leading-snug truncate">{{ $doc->title }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono block truncate">{{ $doc->file_name }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#0066FF]/10 text-[#0066FF] shrink-0">
                                v{{ $doc->version }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between bg-[#F5F7FA] px-3 py-2 rounded-lg border border-[#E2E8F0] text-xs">
                            <span class="text-slate-600 font-medium">{{ $doc->domain ? $doc->domain->name : 'Général' }}</span>
                            <span class="text-slate-500 font-mono">{{ $doc->formatted_size }}</span>
                            <span class="text-slate-400 text-[11px]">{{ $doc->created_at->format('d/m/Y') }}</span>
                        </div>

                        @if(!empty($doc->tags))
                            <div class="flex gap-1 flex-wrap pt-0.5">
                                @foreach($doc->tags as $tg)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">#{{ $tg }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end">
                            <form action="{{ route('ged.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Supprimer ce document ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Supprimer</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Aucun document dans cet emplacement.
                    </div>
                @endforelse
            </div>

            @if($documents->hasPages())
                <div class="p-4 border-t border-[#E2E8F0]">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Archiver un Document -->
        <div x-show="uploadModal" class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/60 backdrop-blur-xs" style="display: none;">
            <div @click.outside="uploadModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E8F0] text-xs">
                <h3 class="text-base font-bold text-[#0B0F14] mb-4">Archiver un Document (GED)</h3>
                <form action="{{ route('ged.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="ged-title-input" class="block font-semibold text-[#0B0F14] mb-1">Titre du document *</label>
                        <input id="ged-title-input" type="text" name="title" placeholder="Ex: Devis d'impression validé Société X" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ged-folder-select" class="block font-semibold text-[#0B0F14] mb-1">Dossier de destination</label>
                            <select id="ged-folder-select" name="folder_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                                <option value="">Dossier racine</option>
                                @foreach($folders as $f)
                                    <option value="{{ $f->id }}" {{ $currentFolder && $currentFolder->id == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="ged-domain-select" class="block font-semibold text-[#0B0F14] mb-1">Domaine</label>
                            <select id="ged-domain-select" name="domain_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                                <option value="">Général</option>
                                @foreach($domains as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="ged-tags-input" class="block font-semibold text-[#0B0F14] mb-1">Tags (séparés par des virgules)</label>
                        <input id="ged-tags-input" type="text" name="tags" placeholder="contrat, urgent, facture" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                    <div>
                        <label for="ged-file-input" class="block font-semibold text-[#0B0F14] mb-1">Fichier (PDF, DOCX, XLSX, IMG - Max 20 Mo)</label>
                        <input id="ged-file-input" type="file" name="file" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="uploadModal = false" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0066FF] text-white font-bold hover:bg-[#0052cc]">Enregistrer le document</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Nouveau Dossier -->
        <div x-show="newFolderModal" class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/60 backdrop-blur-xs" style="display: none;">
            <div @click.outside="newFolderModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-[#E2E8F0] text-xs">
                <h3 class="text-base font-bold text-[#0B0F14] mb-3">Nouveau Dossier</h3>
                <form action="{{ route('ged.folders.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="folder-name-input" class="block font-semibold text-[#0B0F14] mb-1">Nom du dossier *</label>
                        <input id="folder-name-input" type="text" name="name" placeholder="Ex: Devis Grands Comptes 2026" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="newFolderModal = false" class="px-3 py-1.5 rounded-lg border border-[#E2E8F0] text-slate-600 hover:bg-slate-50">Annuler</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#0066FF] text-white font-bold hover:bg-[#0052cc]">Créer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
