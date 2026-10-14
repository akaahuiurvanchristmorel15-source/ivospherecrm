/*
 * Bouton « Installer l'application » (PWA).
 * Android / Chrome / Edge : on capture l'événement beforeinstallprompt pour
 * déclencher l'invite native au clic. iOS Safari n'a pas d'invite : on affiche
 * la marche à suivre (Partager → Sur l'écran d'accueil).
 */
let deferredPrompt = null;

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

const isIos = () => /iPhone|iPad|iPod/.test(navigator.userAgent)
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredPrompt = event;
    window.dispatchEvent(new CustomEvent('pwa-install-available'));
});

window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    window.dispatchEvent(new CustomEvent('pwa-installed'));
});

export function registerPwaInstall(Alpine) {
    Alpine.data('pwaInstall', () => ({
        canPrompt: deferredPrompt !== null,
        installed: isStandalone(),
        ios: isIos(),
        iosHelpOpen: false,

        get visible() {
            return !this.installed && (this.canPrompt || this.ios);
        },

        async install() {
            if (this.ios || !deferredPrompt) {
                this.iosHelpOpen = !this.iosHelpOpen;

                return;
            }

            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            this.canPrompt = false;
            this.installed = outcome === 'accepted';
        },
    }));
}
