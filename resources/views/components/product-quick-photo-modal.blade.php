@props([])

<div
    x-data="productQuickPhotoModal()"
    @open-product-photo.window="open($event.detail)"
    @keydown.escape.window="close()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-quick-photo-title"
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    {{-- Arrière-plan flouté --}}
    <div 
        x-show="isOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#0B0F14]/70 backdrop-blur-xs transition-opacity"
        @click="close()"
    ></div>

    <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
        <div 
            x-show="isOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-200"
            @click.stop
        >
            {{-- En-tête du modal --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#0066FF]/10 text-[#0066FF] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-quick-photo-title" class="text-sm font-bold text-[#0B0F14]">Photo & Visuel du Produit</h3>
                        <p class="text-[11px] text-[#64748B]">Prenez une photo en direct ou téléversez un visuel</p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="close()" 
                    class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Formulaire d'upload / Capture photo --}}
            <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
                @csrf
                <input type="hidden" name="delete_image" :value="deleteImage ? '1' : '0'">

                <div class="p-5 space-y-4">
                    {{-- Fiche résumé du produit ciblé --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="font-mono text-[10px] font-bold text-[#0066FF] bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded" x-text="productSku"></span>
                            </div>
                            <h4 class="text-xs font-bold text-[#0B0F14] truncate" x-text="productName"></h4>
                        </div>
                        <template x-if="hasNewImage">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Non enregistré
                            </span>
                        </template>
                    </div>

                    {{-- Zone d'aperçu d'image en grand --}}
                    <div class="relative w-full aspect-16/10 rounded-2xl border-2 border-dashed border-[#CBD5E1] bg-[#F8FAFC] overflow-hidden flex items-center justify-center group shadow-inner">
                        <template x-if="imagePreview">
                            <div class="relative w-full h-full flex items-center justify-center bg-slate-900/5">
                                <img :src="imagePreview" alt="Aperçu produit" class="w-full h-full object-contain">
                                <div class="absolute bottom-2 left-2 px-2 py-1 bg-black/60 backdrop-blur-xs text-white text-[10px] rounded font-medium" x-text="hasNewImage ? '📸 Aperçu nouvelle photo' : '🖼️ Photo actuelle'"></div>
                            </div>
                        </template>

                        <template x-if="!imagePreview">
                            <div class="text-center p-6 text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-2 rounded-2xl bg-slate-200/60 flex items-center justify-center text-slate-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-600">Aucune photo pour cet article</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Prenez une photo en direct ou choisissez une image depuis votre galerie</p>
                            </div>
                        </template>
                    </div>

                    {{-- Boutons d'action pour prise de photo & choix galerie --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        {{-- 1. Bouton Appareil Photo Smartphone (capture="environment") --}}
                        <label class="relative flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl bg-[#0066FF] hover:bg-[#0052cc] text-white font-bold text-xs cursor-pointer transition-all shadow-sm active:scale-98">
                            <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <circle cx="12" cy="13" r="3" stroke-width="2" />
                            </svg>
                            <div class="text-left">
                                <div class="leading-tight">Prendre une photo</div>
                                <div class="text-[9.5px] font-normal text-blue-100">Appareil photo smartphone</div>
                            </div>
                            <input 
                                type="file" 
                                name="image_camera" 
                                x-ref="cameraInput" 
                                @change="onImageSelected($event, 'camera')" 
                                accept="image/*" 
                                capture="environment" 
                                class="sr-only"
                            >
                        </label>

                        {{-- 2. Bouton Galerie / Fichiers --}}
                        <label class="relative flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl bg-white border border-[#CBD5E1] hover:border-[#0066FF] hover:text-[#0066FF] text-[#0B0F14] font-bold text-xs cursor-pointer transition-all shadow-2xs active:scale-98">
                            <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <div class="text-left">
                                <div class="leading-tight">Choisir un fichier</div>
                                <div class="text-[9.5px] font-normal text-slate-500">Galerie / Ordinateur</div>
                            </div>
                            <input 
                                type="file" 
                                name="image" 
                                x-ref="fileInput" 
                                @change="onImageSelected($event, 'file')" 
                                accept="image/png,image/jpeg,image/webp,image/svg+xml" 
                                class="sr-only"
                            >
                        </label>
                    </div>

                    {{-- Option de suppression si une image existe déjà et qu'aucune nouvelle n'est sélectionnée --}}
                    <div x-show="initialImage && !hasNewImage && !deleteImage" class="pt-1 text-center">
                        <button 
                            type="button" 
                            @click="markDeleteImage()" 
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-800 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Supprimer la photo actuelle</span>
                        </button>
                    </div>

                    {{-- Bannière d'avertissement de suppression --}}
                    <div x-show="deleteImage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
                        <span>La photo actuelle sera définitivement supprimée à l'enregistrement.</span>
                        <button type="button" @click="cancelDelete()" class="font-bold underline text-rose-900 ml-2">Annuler</button>
                    </div>
                </div>

                {{-- Pied de page du Modal avec actions de validation --}}
                <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3.5 bg-slate-50/70">
                    <button 
                        type="button" 
                        @click="close()" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 transition"
                    >
                        Fermer
                    </button>

                    <div class="flex items-center gap-2">
                        <template x-if="hasNewImage">
                            <button 
                                type="button" 
                                @click="resetSelection()" 
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/60 transition"
                            >
                                Réinitialiser
                            </button>
                        </template>

                        <button 
                            type="submit" 
                            :disabled="isSubmitting || (!hasNewImage && !deleteImage)" 
                            :class="{'opacity-50 cursor-not-allowed': (!hasNewImage && !deleteImage) || isSubmitting}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition active:scale-95"
                        >
                            <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span x-text="deleteImage ? 'Confirmer la suppression' : (hasNewImage ? 'Enregistrer la photo' : 'Aucune modification')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function productQuickPhotoModal() {
        return {
            isOpen: false,
            isSubmitting: false,
            productId: null,
            productName: '',
            productSku: '',
            initialImage: '',
            imagePreview: '',
            hasNewImage: false,
            deleteImage: false,
            actionUrl: '',

            open(detail) {
                this.productId = detail.id || null;
                this.productName = detail.name || '';
                this.productSku = detail.sku || '';
                this.initialImage = detail.current_image || '';
                this.imagePreview = this.initialImage;
                this.hasNewImage = false;
                this.deleteImage = false;
                this.isSubmitting = false;
                this.actionUrl = detail.action_url || '';

                if (this.$refs.cameraInput) this.$refs.cameraInput.value = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';

                this.isOpen = true;
            },

            close() {
                this.isOpen = false;
                this.hasNewImage = false;
                this.deleteImage = false;
            },

            onImageSelected(event, source) {
                const file = event.target.files[0];
                if (!file) return;

                this.deleteImage = false;
                this.hasNewImage = true;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);

                // Réinitialiser l'autre input
                if (source === 'camera' && this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                } else if (source === 'file' && this.$refs.cameraInput) {
                    this.$refs.cameraInput.value = '';
                }
            },

            markDeleteImage() {
                this.deleteImage = true;
                this.hasNewImage = false;
                this.imagePreview = '';
                if (this.$refs.cameraInput) this.$refs.cameraInput.value = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },

            cancelDelete() {
                this.deleteImage = false;
                this.imagePreview = this.initialImage;
            },

            resetSelection() {
                this.hasNewImage = false;
                this.deleteImage = false;
                this.imagePreview = this.initialImage;
                if (this.$refs.cameraInput) this.$refs.cameraInput.value = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            }
        };
    }
</script>
