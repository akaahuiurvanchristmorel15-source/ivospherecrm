/*
 * Bouton « Installer l'application » (PWA).
 * Quand le navigateur le permet (Chrome, Edge, Samsung Internet en HTTPS), on
 * capture beforeinstallprompt pour ouvrir l'invite native au clic. Sinon
 * (iPhone, connexion non sécurisée, autre navigateur), le bouton reste visible
 * sur mobile et affiche la marche à suivre adaptée.
 */
let deferredPrompt = null;

const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

const isIos = () => /iPhone|iPad|iPod/.test(navigator.userAgent)
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

const isMobile = () => isIos() || /Android|Mobi/i.test(navigator.userAgent);

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
        mobile: isMobile(),
        secure: window.isSecureContext,
        helpOpen: false,

        get visible() {
            return !this.installed && (this.canPrompt || this.mobile);
        },

        async install() {
            if (!deferredPrompt) {
                this.helpOpen = !this.helpOpen;

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
