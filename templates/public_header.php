<?php require_once __DIR__ . '/head.php'; ?>
    <header class="bg-white border-b border-slate-200 py-3 shadow-[0_1px_3px_0_rgba(11,31,58,0.02)] relative z-50">
        <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12 flex justify-between items-center">
            <a href="/" class="font-bold text-2xl text-primary tracking-tight flex items-center gap-2">
                <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 12l10 10 10-10L12 2zm0 14.5L7.5 12 12 7.5 16.5 12 12 16.5z"/></svg>
                <?= escape_html($settings['nom']) ?>
            </a>
            
            <nav class="hidden md:flex gap-8 items-center">
                <button type="button" id="services-modal-btn" aria-expanded="false" aria-controls="services-modal" class="font-semibold text-slate-600 hover:text-primary transition-colors flex items-center gap-1">
                    Services
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <a href="/track" class="font-semibold text-slate-600 hover:text-primary transition-colors">Suivre un colis</a>
                <a href="/contact" class="font-semibold text-slate-600 hover:text-primary transition-colors">Contact</a>
                <a href="/login" class="btn-ghost py-2 min-h-0 h-10 ml-4">Espace Pro</a>
            </nav>

            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors" aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <!-- Menu Mobile -->
        <div id="mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full bg-white border-b border-slate-200 shadow-elevation-2">
            <nav class="flex flex-col py-2">
                <button type="button" id="mobile-services-btn" class="text-left font-semibold px-6 py-4 text-slate-700 hover:bg-slate-50 transition-colors flex justify-between items-center border-b border-slate-100">
                    Services
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                <a href="/track" class="font-semibold px-6 py-4 text-slate-700 hover:bg-slate-50 transition-colors border-b border-slate-100">Suivre un colis</a>
                <a href="/contact" class="font-semibold px-6 py-4 text-slate-700 hover:bg-slate-50 transition-colors">Contact</a>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                    <a href="/login" class="btn-ghost w-full">Espace Pro</a>
                </div>
            </nav>
        </div>
    </header>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
                mobileMenu.classList.toggle('hidden');
                mobileMenuBtn.setAttribute('aria-expanded', !isExpanded);
            });
        }
    </script>
