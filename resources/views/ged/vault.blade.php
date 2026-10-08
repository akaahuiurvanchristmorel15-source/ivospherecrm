<x-layouts.app title="Coffre-Fort Numérique Sécurisé">
    <div class="space-y-6" x-data="{ uploadVaultModal: false }">

        <!-- Top Header with Dark High-Security Badge -->
        <div class="p-6 rounded-2xl bg-[#0B0F14] text-white flex flex-col md:flex-row md:items-center justify-between gap-4 border border-slate-800 shadow-xl">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">Espace Haute Confidentialité</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                    <span>Coffre-Fort Numérique IVOSPHERE</span>
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </h1>
                <p class="text-xs text-slate-400">
                    Stockage chiffré réservé aux documents stratégiques (Bilans fiscaux, statuts légaux, contrats directeurs & pièces d'identité).
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('ged.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Retour à la GED</span>
                </a>

                <button @click="uploadVaultModal = true" class="px-4 py-2 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white text-xs font-bold shadow-md shadow-[#0066FF]/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Déposer au Coffre</span>
                </button>
            </div>
        </div>

        <!-- Vault Folders & Categories -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 text-amber-400 flex items-center justify-center font-bold text-sm">
                        ⚖️
                    </div>
                    <div>
                        <p class="font-bold text-xs text-[#0B0F14]">Statuts & Registres</p>
                        <p class="text-[11px] text-slate-400">Actes constitutifs légaux</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 text-emerald-400 flex items-center justify-center font-bold text-sm">
                        📊
                    </div>
                    <div>
                        <p class="font-bold text-xs text-[#0B0F14]">Bilans & Fiscaux</p>
                        <p class="text-[11px] text-slate-400">États financiers certifiés</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 text-blue-400 flex items-center justify-center font-bold text-sm">
                        🔐
                    </div>
                    <div>
                        <p class="font-bold text-xs text-[#0B0F14]">Mandataires & CNI</p>
                        <p class="text-[11px] text-slate-400">Pièces d'identité direction</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-white border border-[#E2E8F0] shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 text-purple-400 flex items-center justify-center font-bold text-sm">
                        📑
                    </div>
                    <div>
                        <p class="font-bold text-xs text-[#0B0F14]">Baux Commerciaux</p>
                        <p class="text-[11px] text-slate-400">Contrats des sièges & sites</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secure Documents Table -->
        <div class="bg-white rounded-2xl border border-[#E2E8F0] shadow-xs overflow-hidden">
            <div class="p-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#0066FF]"></span>
                    <h2 class="text-xs font-bold text-[#0B0F14] uppercase tracking-wider">Actifs Sécurisés sous Traçabilité</h2>
                </div>
                <span class="text-xs text-slate-400">{{ $documents->total() }} fichier(s) classifiés</span>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs text-[#0B0F14]">
                    <thead class="bg-[#F5F7FA] border-b border-[#E2E8F0] text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-4">Intitulé Sécurisé</th>
                            <th class="py-3 px-4">Taille</th>
                            <th class="py-3 px-4">Version</th>
                            <th class="py-3 px-4">Dépositaire</th>
                            <th class="py-3 px-4">Date de Dépôt</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documents as $doc)
                            <tr class="hover:bg-[#F5F7FA] transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <span class="p-1.5 rounded-lg bg-slate-900 text-amber-400 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </span>
                                        <div>
                                            <span class="font-bold text-[#0B0F14] block">{{ $doc->title }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $doc->file_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                    {{ $doc->formatted_size }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#0066FF]">
                                    v{{ $doc->version }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-[#0B0F14]">{{ $doc->user ? $doc->user->name : 'Direction' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-[11px] text-slate-400">
                                    {{ $doc->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <form action="{{ route('ged.documents.destroy', $doc) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmer la suppression sécurisée de ce document ?');">
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
                                <td colspan="6" class="py-16 text-center text-slate-400">
                                    Le coffre-fort ne contient aucun document pour le moment.
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
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-lg bg-slate-900 text-amber-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
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
                            <span>Dépositaire : <strong class="text-slate-800">{{ $doc->user ? $doc->user->name : 'Direction' }}</strong></span>
                            <span class="text-slate-500 font-mono">{{ $doc->formatted_size }}</span>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                            <span>Scellé le {{ $doc->created_at->format('d/m/Y H:i') }}</span>
                            <form action="{{ route('ged.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Confirmer la suppression sécurisée de ce document ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        Le coffre-fort ne contient aucun document pour le moment.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Modal: Déposer au Coffre -->
        <div x-show="uploadVaultModal" class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-[#0B0F14]/70 backdrop-blur-xs" style="display: none;">
            <div @click.outside="uploadVaultModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E2E8F0] text-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-[#E2E8F0] mb-4">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <h3 class="text-base font-bold text-[#0B0F14]">Dépôt Sécurisé au Coffre-Fort</h3>
                </div>

                <form action="{{ route('ged.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="is_vault" value="1" />

                    <div>
                        <label for="vault-title-input" class="block font-semibold text-[#0B0F14] mb-1">Intitulé du document classifié *</label>
                        <input id="vault-title-input" type="text" name="title" placeholder="Ex: Bilan Comptable Exercice 2025 Certifié" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" required />
                    </div>

                    <div>
                        <label for="vault-domain-select" class="block font-semibold text-[#0B0F14] mb-1">Pôle concerné</label>
                        <select id="vault-domain-select" name="domain_id" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]">
                            <option value="">Direction Générale</option>
                            @foreach($domains as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="vault-tags-input" class="block font-semibold text-[#0B0F14] mb-1">Tags de recherche</label>
                        <input id="vault-tags-input" type="text" name="tags" placeholder="fiscal, bilan, confidentiel" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>

                    <div>
                        <label for="vault-file-input" class="block font-semibold text-[#0B0F14] mb-1">Fichier crypté (PDF, DOCX, XLSX)</label>
                        <input id="vault-file-input" type="file" name="file" class="w-full px-3 py-2 rounded-lg bg-[#F5F7FA] border border-[#E2E8F0]" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
                        <button type="button" @click="uploadVaultModal = false" class="px-4 py-2 rounded-lg border border-[#E2E8F0] text-slate-600 hover:bg-slate-50 font-medium">Annuler</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#0B0F14] text-white font-bold hover:bg-slate-800">Sceller au Coffre</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
