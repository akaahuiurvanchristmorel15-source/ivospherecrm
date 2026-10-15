{{-- Définit window.compressProductImage() : réduit une photo (souvent 3 à 10 Mo sur téléphone) en JPEG ≤ 1600 px.
     En cas d'échec (format non lisible par le navigateur), le fichier d'origine est renvoyé. --}}
@once
<script>
    window.compressProductImage = async function (file, maxDimension = 1600) {
        if (!file || !file.type || !file.type.startsWith('image/') || ['image/svg+xml', 'image/gif'].includes(file.type)) {
            return file;
        }

        const objectUrl = URL.createObjectURL(file);
        try {
            const image = new Image();
            image.src = objectUrl;
            await image.decode();

            const scale = Math.min(1, maxDimension / Math.max(image.naturalWidth, image.naturalHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
            const context = canvas.getContext('2d');
            context.fillStyle = '#FFFFFF';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);

            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.85));
            if (!blob || (blob.size >= file.size && scale === 1 && ['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
                return file;
            }

            const baseName = (file.name || 'photo').replace(/\.[^.]+$/, '');
            return new File([blob], baseName + '.jpg', { type: 'image/jpeg' });
        } catch (error) {
            return file;
        } finally {
            URL.revokeObjectURL(objectUrl);
        }
    };
</script>
@endonce
