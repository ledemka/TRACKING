<?php require_once __DIR__ . '/head.php'; ?>
    <header class="bg-primary text-white py-4 shadow-sm relative z-50">
        <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12 flex justify-between items-center">
            <a href="/" class="font-bold text-xl tracking-tight"><?= escape_html($settings['nom']) ?></a>
            
            <nav class="hidden md:flex gap-6 items-center">
                <a href="/" class="hover:text-accent transition-colors">Accueil</a>
                <a href="/track" class="hover:text-accent transition-colors">Suivre un colis</a>
                <a href="/contact" class="hover:text-accent transition-colors">Contact</a>
                <a href="/login" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors text-sm font-semibold">Connexion</a>
            </nav>

            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <!-- Menu Mobile -->
        <div id="mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full bg-primary border-t border-white/10 shadow-lg">
            <nav class="flex flex-col py-2">
                <a href="/" class="px-4 py-3 hover:bg-white/5 transition-colors">Accueil</a>
                <a href="/track" class="px-4 py-3 hover:bg-white/5 transition-colors">Suivre un colis</a>
                <a href="/contact" class="px-4 py-3 hover:bg-white/5 transition-colors">Contact</a>
                <div class="px-4 py-3 border-t border-white/10">
                    <a href="/login" class="block text-center bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors text-sm font-semibold">Connexion</a>
                </div>
            </nav>
        </div>
    </header>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
