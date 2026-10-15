{{-- window.processProductImage(file) : décode la photo une seule fois et renvoie
     { file } (JPEG ≤ 1600 px à envoyer) et { preview } (petit JPEG ≤ 200 px pour l'aperçu).
     Une photo de téléphone décodée occupe ~50 Mo : la mémoire est libérée aussitôt, sinon
     l'onglet est fermé par Android (page blanche).
     window.compressProductImage(file) ne renvoie que le fichier à envoyer.
     En cas d'échec (format non lisible par le navigateur), le fichier d'origine est renvoyé. --}}
@once
<script>
    (function () {
        const canvasToBlob = (canvas, quality) => new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));

        const drawScaled = (source, width, height, maxDimension) => {
            const scale = Math.min(1, maxDimension / Math.max(width, height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(width * scale));
            canvas.height = Math.max(1, Math.round(height * scale));
            const context = canvas.getContext('2d');
            context.fillStyle = '#FFFFFF';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(source, 0, 0, canvas.width, canvas.height);
            return { canvas, scale };
        };

        /**
         * Décode la photo. createImageBitmap permet de libérer la mémoire tout de suite (close()),
         * alors qu'une balise <img> la garde jusqu'au passage du ramasse-miettes.
         */
        const decode = async (file) => {
            if (typeof createImageBitmap === 'function') {
                try {
                    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
                    return { source: bitmap, width: bitmap.width, height: bitmap.height, release: () => bitmap.close() };
                } catch (error) {
                    // Navigateur sans prise en charge de ce format via createImageBitmap : repli sur <img>
                }
            }

            const objectUrl = URL.createObjectURL(file);
            const image = new Image();
            image.src = objectUrl;
            try {
                await image.decode();
            } catch (error) {
                image.src = '';
                URL.revokeObjectURL(objectUrl);
                throw error;
            }
            return {
                source: image,
                width: image.naturalWidth,
                height: image.naturalHeight,
                release: () => { image.src = ''; URL.revokeObjectURL(objectUrl); },
            };
        };

        window.processProductImage = async function (file, maxDimension = 1600, previewDimension = 200) {
            if (!file || !file.type || !file.type.startsWith('image/') || ['image/svg+xml', 'image/gif'].includes(file.type)) {
                return { file, preview: file };
            }

            let decoded = null;
            try {
                decoded = await decode(file);
                const main = drawScaled(decoded.source, decoded.width, decoded.height, maxDimension);
                const preview = drawScaled(decoded.source, decoded.width, decoded.height, previewDimension);
                decoded.release();
                decoded = null;

                const [mainBlob, previewBlob] = await Promise.all([canvasToBlob(main.canvas, 0.85), canvasToBlob(preview.canvas, 0.7)]);
                main.canvas.width = main.canvas.height = 0;
                preview.canvas.width = preview.canvas.height = 0;

                const keepOriginal = !mainBlob
                    || (mainBlob.size >= file.size && main.scale === 1 && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type));
                const baseName = (file.name || 'photo').replace(/\.[^.]+$/, '');

                return {
                    file: keepOriginal ? file : new File([mainBlob], baseName + '.jpg', { type: 'image/jpeg' }),
                    preview: previewBlob || file,
                };
            } catch (error) {
                return { file, preview: file };
            } finally {
                if (decoded) {
                    decoded.release();
                }
            }
        };

        window.compressProductImage = async function (file, maxDimension = 1600) {
            return (await window.processProductImage(file, maxDimension)).file;
        };
    })();
</script>
@endonce
