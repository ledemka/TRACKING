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
        if (submitText) {
            submitText.textContent = "Recherche de votre colis…";
        }
        if (statusContainer) {
            statusContainer.textContent = "Recherche de votre colis…";
            statusContainer.setAttribute('role', 'status');
        }
    });

    // Restaurer l'état original lors du retour en arrière (BFCache)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            if (submitText) {
                submitText.textContent = "Rechercher";
            }
            if (statusContainer) {
                statusContainer.textContent = "";
                statusContainer.removeAttribute('role');
            }
        }
    });
});
