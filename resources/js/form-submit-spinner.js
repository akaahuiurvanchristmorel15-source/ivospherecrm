/*
 * Spinner d'enregistrement : à l'envoi d'un formulaire (POST, PUT, DELETE…), le bouton cliqué
 * affiche un spinner et le formulaire ne peut plus être renvoyé (double clic).
 * Ignoré pour les recherches (GET), les envois annulés (confirm, @submit.prevent),
 * les formulaires ouverts dans un nouvel onglet, ceux marqués data-no-spinner
 * et les boutons qui ont déjà leur propre spinner (.animate-spin).
 */
const submitButtonSelector = 'button[type="submit"], button:not([type]), input[type="submit"]';

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (event.defaultPrevented || !(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.method === 'get' || form.target === '_blank' || form.hasAttribute('data-no-spinner')) {
        return;
    }

    if (form.hasAttribute('data-submitting')) {
        event.preventDefault();

        return;
    }

    form.setAttribute('data-submitting', '');

    const submitter = event.submitter ?? form.querySelector(submitButtonSelector);

    if (submitter && !submitter.querySelector('.animate-spin')) {
        submitter.setAttribute('data-submitting', '');
        submitter.setAttribute('aria-busy', 'true');
    }
});

// Retour arrière du navigateur (cache de page) : on réactive les boutons
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('[data-submitting]').forEach((element) => {
        element.removeAttribute('data-submitting');
        element.removeAttribute('aria-busy');
    });
});
