// assets/js/nav.js
// Gestion accessible du menu mobile sans script inline
(function() {
    'use strict';

    function initNav() {
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        if (!btn || !menu) return;

        function toggleMenu(show) {
            const isCurrentlyExpanded = btn.getAttribute('aria-expanded') === 'true';
            const willExpand = typeof show === 'boolean' ? show : !isCurrentlyExpanded;

            btn.setAttribute('aria-expanded', willExpand ? 'true' : 'false');
            if (willExpand) {
                menu.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
            }
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });

        // Fermeture sur Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') {
                toggleMenu(false);
                btn.focus();
            }
        });

        // Fermeture au clic en dehors
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !btn.contains(e.target) && btn.getAttribute('aria-expanded') === 'true') {
                toggleMenu(false);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNav);
    } else {
        initNav();
    }
})();
