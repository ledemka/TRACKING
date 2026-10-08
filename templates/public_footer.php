    <footer class="bg-primary pt-16 pb-8 mt-auto border-t-[4px] border-accent">
        <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12">
            <div class="flex flex-col md:flex-row justify-between gap-8 mb-12 w-full">
                <div class="md:w-1/2 pr-0 md:pr-12">
                    <a href="/" class="font-bold text-2xl text-white tracking-tight flex items-center gap-2 mb-4">
                        <svg class="w-8 h-8 text-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12l10 10 10-10L12 2zm0 14.5L7.5 12 12 7.5 16.5 12 12 16.5z"/></svg>
                        <?= escape_html($settings['nom']) ?>
                    </a>
                    <p class="text-slate-300 text-sm leading-relaxed max-w-sm">
                        Suivez vos expéditions en toute simplicité. La garantie d'une logistique de précision, sécurisée et fiable à chaque étape du transit.
                    </p>
                </div>
                <div class="md:w-1/4">
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Accès Rapide</h4>
                    <ul class="space-y-3">
                        <li><a href="/" class="text-slate-400 hover:text-white transition-colors text-sm">Accueil</a></li>
                        <li><a href="/track" class="text-slate-400 hover:text-white transition-colors text-sm">Suivre un colis</a></li>
                        <li><a href="/contact" class="text-slate-400 hover:text-white transition-colors text-sm">Contactez-nous</a></li>
                    </ul>
                </div>
                <div class="md:w-1/4">
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Services</h4>
                    <ul class="space-y-3">
                        <li><a href="/services/expedition" class="text-slate-400 hover:text-white transition-colors text-sm">Expédition</a></li>
                        <li><a href="/services/livraison" class="text-slate-400 hover:text-white transition-colors text-sm">Livraison</a></li>
                        <li><a href="/login" class="text-slate-400 hover:text-white transition-colors text-sm">Espace Pro</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- MODAL SERVICES -->
    <div id="services-modal" class="fixed inset-0 hidden" style="z-index: 9999;" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="fixed inset-0 bg-primary/40 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop" aria-hidden="true"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div id="modal-panel" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-elevation-4 transition-all opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 sm:my-8 w-full max-w-2xl">
                    <div class="bg-white px-6 pb-4 pt-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-xl font-bold leading-6 text-primary" id="modal-title">Nos services logistiques</h3>
                        <button type="button" id="close-modal-btn" aria-label="Fermer le modal" class="rounded-md bg-white text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-action p-2 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="/services/expedition" class="group flex flex-col p-4 rounded-xl border border-slate-200 hover:border-action/30 hover:shadow-elevation-2 hover:bg-slate-50 transition-all text-left focus:outline-none focus:ring-2 focus:ring-action">
                                <div class="w-12 h-12 bg-surface rounded-xl flex items-center justify-center text-slate-400 group-hover:text-action group-hover:bg-action/10 transition-colors mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <h4 class="font-bold text-slate-900 mb-2 group-hover:text-action transition-colors">Expédition</h4>
                                <p class="text-sm text-slate-500 line-clamp-2">Préparez et expédiez vos colis simplement avec un suivi à chaque étape.</p>
                            </a>
                            <a href="/services/suivi" class="group flex flex-col p-4 rounded-xl border border-slate-200 hover:border-action/30 hover:shadow-elevation-2 hover:bg-slate-50 transition-all text-left focus:outline-none focus:ring-2 focus:ring-action">
                                <div class="w-12 h-12 bg-surface rounded-xl flex items-center justify-center text-slate-400 group-hover:text-action group-hover:bg-action/10 transition-colors mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <h4 class="font-bold text-slate-900 mb-2 group-hover:text-action transition-colors">Suivi des colis</h4>
                                <p class="text-sm text-slate-500 line-clamp-2">Consultez l'état de votre expédition à l'aide de votre numéro de suivi.</p>
                            </a>
                            <a href="/services/livraison" class="group flex flex-col p-4 rounded-xl border border-slate-200 hover:border-action/30 hover:shadow-elevation-2 hover:bg-slate-50 transition-all text-left focus:outline-none focus:ring-2 focus:ring-action">
                                <div class="w-12 h-12 bg-surface rounded-xl flex items-center justify-center text-slate-400 group-hover:text-action group-hover:bg-action/10 transition-colors mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                </div>
                                <h4 class="font-bold text-slate-900 mb-2 group-hover:text-action transition-colors">Livraison</h4>
                                <p class="text-sm text-slate-500 line-clamp-2">Suivez l'avancement de votre colis jusqu'à sa livraison.</p>
                            </a>
                            <a href="/services/gestion-expeditions" class="group flex flex-col p-4 rounded-xl border border-slate-200 hover:border-action/30 hover:shadow-elevation-2 hover:bg-slate-50 transition-all text-left focus:outline-none focus:ring-2 focus:ring-action">
                                <div class="w-12 h-12 bg-surface rounded-xl flex items-center justify-center text-slate-400 group-hover:text-action group-hover:bg-action/10 transition-colors mb-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                                </div>
                                <h4 class="font-bold text-slate-900 mb-2 group-hover:text-action transition-colors">Gestion des expéditions</h4>
                                <p class="text-sm text-slate-500 line-clamp-2">Une interface dédiée pour gérer vos colis et leurs informations de suivi.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('services-modal');
            const backdrop = document.getElementById('modal-backdrop');
            const panel = document.getElementById('modal-panel');
            const closeBtn = document.getElementById('close-modal-btn');
            
            const openBtns = [document.getElementById('services-modal-btn'), document.getElementById('mobile-services-btn')].filter(Boolean);
            
            // Éléments focusables pour la navigation clavier (tab-trap)
            const focusableElementsString = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable]';
            
            let lastFocusedElement = null;

            function openModal(opener) {
                lastFocusedElement = opener || document.activeElement;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Bloquer le scroll
                
                openBtns.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
                
                // Animation d'ouverture
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                    panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                    closeBtn.focus();
                }, 10);
            }

            function closeModal() {
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                
                panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
                panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                
                openBtns.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                    if (lastFocusedElement) {
                        lastFocusedElement.focus();
                    }
                }, 300); // durée de la transition
            }

            openBtns.forEach(btn => {
                btn.addEventListener('click', () => openModal(btn));
            });

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (backdrop) backdrop.addEventListener('click', closeModal);
            
            // Fermeture via Escape et tab-trap
            document.addEventListener('keydown', (e) => {
                if (modal.classList.contains('hidden')) return;

                if (e.key === 'Escape') {
                    closeModal();
                } else if (e.key === 'Tab') {
                    const focusableElements = modal.querySelectorAll(focusableElementsString);
                    const firstFocusable = focusableElements[0];
                    const lastFocusable = focusableElements[focusableElements.length - 1];

                    if (e.shiftKey) { // Shift + Tab
                        if (document.activeElement === firstFocusable) {
                            lastFocusable.focus();
                            e.preventDefault();
                        }
                    } else { // Tab
                        if (document.activeElement === lastFocusable) {
                            firstFocusable.focus();
                            e.preventDefault();
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
