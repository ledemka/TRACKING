// assets/js/tabs.js
// Gestion accessible des onglets (ARIA Tabs Pattern : clavier flèches, Home, End, aria-selected, aria-controls, focus)
(function() {
    'use strict';

    function initQualityTabs() {
        const tabList = document.querySelector('[role="tablist"][data-tabs]');
        if (!tabList) return;

        const tabs = Array.from(tabList.querySelectorAll('[role="tab"]'));
        const panels = tabs.map(tab => {
            const panelId = tab.getAttribute('aria-controls');
            return document.getElementById(panelId);
        });

        if (tabs.length === 0) return;

        function activateTab(targetTab, setFocus = true) {
            tabs.forEach((tab, index) => {
                const isTarget = (tab === targetTab);
                const panel = panels[index];

                tab.setAttribute('aria-selected', isTarget ? 'true' : 'false');
                tab.setAttribute('tabindex', isTarget ? '0' : '-1');

                if (panel) {
                    if (isTarget) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }
                }
            });

            if (setFocus) {
                targetTab.focus();
            }
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                activateTab(this, false);
            });
        });

        // Navigation au clavier conforme WAI-ARIA
        tabList.addEventListener('keydown', function(e) {
            const currentIndex = tabs.indexOf(document.activeElement);
            if (currentIndex === -1) return;

            let nextIndex = null;

            switch (e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    nextIndex = (currentIndex + 1) % tabs.length;
                    break;
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
                    break;
                case 'Home':
                    e.preventDefault();
                    nextIndex = 0;
                    break;
                case 'End':
                    e.preventDefault();
                    nextIndex = tabs.length - 1;
                    break;
                default:
                    return;
            }

            if (nextIndex !== null) {
                activateTab(tabs[nextIndex], true);
            }
        });

        // Activation de l'onglet initial
        const initialSelectedTab = tabs.find(t => t.getAttribute('aria-selected') === 'true') || tabs[0];
        activateTab(initialSelectedTab, false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQualityTabs);
    } else {
        initQualityTabs();
    }
})();
