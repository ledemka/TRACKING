// admin.js
document.addEventListener('DOMContentLoaded', () => {
    // Generate tracking number
    const btnGenerate = document.getElementById('btn-generate-tracking');
    if (btnGenerate) {
        btnGenerate.addEventListener('click', (e) => {
            e.preventDefault();
            const input = document.getElementById('tracking_number');
            if (input) {
                const randomLetters = Array.from({length: 4}, () => String.fromCharCode(65 + Math.floor(Math.random() * 26))).join('');
                const randomNumbers = Array.from({length: 5}, () => Math.floor(Math.random() * 10)).join('');
                input.value = `COLIS-${randomLetters}-${randomNumbers}`;
            }
        });
    }

    // Modal dialog handling for deletion
    const deleteButtons = document.querySelectorAll('.btn-delete-prompt');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const dialogId = btn.getAttribute('data-dialog');
            const dialog = document.getElementById(dialogId);
            if (dialog) {
                dialog.showModal();
            }
        });
    });

    const closeButtons = document.querySelectorAll('.btn-close-dialog');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const dialog = btn.closest('dialog');
            if (dialog) {
                dialog.close();
            }
        });
    });
    
    // Fallback if no JS for modal? The form handles it with a checkbox.
    // If JS is enabled, we could auto-check the hidden checkbox when submitting from dialog,
    // or just let the dialog form contain the checkbox (which the user must check, or we hide it).
    // The prompt says: "sans JS, le formulaire exige de cocher 'Je confirme la suppression' (validé côté serveur)"
    // If JS is present, we can just check it automatically right before submitting from the modal, or keep it required in the modal.
    const deleteForms = document.querySelectorAll('.delete-form-js-modal');
    deleteForms.forEach(form => {
        form.addEventListener('submit', () => {
            const checkbox = form.querySelector('.confirm-checkbox');
            if (checkbox) {
                checkbox.checked = true; // Auto-check if submitted via JS modal
            }
        });
    });

    // Company fields toggle
    const isCompanyCb = document.getElementById('is_company_cb');
    const companyFieldsContainer = document.getElementById('company_fields_container');
    if (isCompanyCb && companyFieldsContainer) {
        isCompanyCb.addEventListener('change', () => {
            if (isCompanyCb.checked) {
                companyFieldsContainer.classList.remove('hidden');
            } else {
                companyFieldsContainer.classList.add('hidden');
                // Nettoyage des champs si décoché
                const companyName = document.getElementById('company_name_input');
                const companySiret = document.getElementById('company_siret_input');
                const companyDept = document.getElementById('company_department_input');
                if(companyName) companyName.value = '';
                if(companySiret) companySiret.value = '';
                if(companyDept) companyDept.value = '';
            }
        });
    }

    // Menu Burger Mobile pour l'administration
    const burgerBtn = document.getElementById('admin-burger-btn');
    const burgerIcon = document.getElementById('admin-burger-icon');
    const mobileMenu = document.getElementById('admin-mobile-menu');

    if (burgerBtn && mobileMenu) {
        burgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isExpanded = burgerBtn.getAttribute('aria-expanded') === 'true';
            const willOpen = !isExpanded;

            burgerBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            burgerBtn.setAttribute('aria-label', willOpen ? 'Fermer le menu de navigation' : 'Ouvrir le menu de navigation');

            if (willOpen) {
                mobileMenu.classList.remove('hidden');
                if (burgerIcon) burgerIcon.textContent = 'close';
            } else {
                mobileMenu.classList.add('hidden');
                if (burgerIcon) burgerIcon.textContent = 'menu';
            }
        });

        // Fermer le menu si on clique en dehors
        document.addEventListener('click', (e) => {
            if (!mobileMenu.classList.contains('hidden') && !mobileMenu.contains(e.target) && !burgerBtn.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                burgerBtn.setAttribute('aria-expanded', 'false');
                burgerBtn.setAttribute('aria-label', 'Ouvrir le menu de navigation');
                if (burgerIcon) burgerIcon.textContent = 'menu';
            }
        });

        // Fermer avec la touche Échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
                burgerBtn.setAttribute('aria-expanded', 'false');
                burgerBtn.setAttribute('aria-label', 'Ouvrir le menu de navigation');
                if (burgerIcon) burgerIcon.textContent = 'menu';
                burgerBtn.focus();
            }
        });
    }
});
