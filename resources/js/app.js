import './form-submit-spinner';
import { registerPwaInstall } from './pwa-install';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Une seule copie d'Alpine pour toute l'application : celle de Livewire, qui la démarre
window.Alpine = Alpine;

registerPwaInstall(Alpine);

Livewire.start();

// Service worker : rend l'application installable sur smartphone (PWA)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    });
}
