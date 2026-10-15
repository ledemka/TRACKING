document.addEventListener('DOMContentLoaded', () => {
    const trackForm = document.getElementById('track-form');
    if (!trackForm) return;

    const submitBtn = document.getElementById('track-submit-btn');
    const submitText = document.getElementById('track-submit-text');
    const statusContainer = document.getElementById('track-status-container');

    trackForm.addEventListener('submit', () => {
        // Désactiver le bouton
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');

        // Mettre à jour le texte du bouton ou afficher un statut
        const loadingText = (window.I18N && window.I18N['track.searching_colis']) 
            || (submitBtn && submitBtn.dataset.loadingText) 
            || "Recherche de votre colis…";

        if (submitText) {
            submitText.textContent = loadingText;
        }
        if (statusContainer) {
            statusContainer.textContent = loadingText;
            statusContainer.setAttribute('role', 'status');
        }
    });

    // Restaurer l'état original lors du retour en arrière (BFCache)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            const defaultText = (window.I18N && window.I18N['track.search_button']) 
                || (submitBtn && submitBtn.dataset.defaultText) 
                || "Rechercher";
            if (submitText) {
                submitText.textContent = defaultText;
            }
            if (statusContainer) {
                statusContainer.textContent = "";
                statusContainer.removeAttribute('role');
            }
        }
    });
});
