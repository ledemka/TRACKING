<?php require_once __DIR__ . '/head.php'; ?>
    <!-- Sidebar Desktop -->
    <aside class="hidden md:flex flex-col w-64 bg-primary text-white flex-shrink-0 min-h-screen">
        <div class="p-6">
            <a href="/dashboard" class="font-bold text-xl tracking-tight"><?= escape_html($settings['nom']) ?></a>
        </div>
        <nav class="flex-1 mt-6 flex flex-col gap-2 px-4">
            <a href="/dashboard" class="px-4 py-3 bg-white/10 rounded-lg text-white font-semibold">Tableau de bord</a>
            <a href="/shipments" class="px-4 py-3 text-white/70 hover:bg-white/5 hover:text-white rounded-lg transition-colors">Expéditions</a>
            <a href="/settings" class="px-4 py-3 text-white/70 hover:bg-white/5 hover:text-white rounded-lg transition-colors">Paramètres</a>
        </nav>
        <div class="p-4 mt-auto">
            <form action="/logout" method="POST" class="w-full">
                <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
                <button type="submit" class="flex w-full items-center justify-center px-4 py-2 border border-white/20 rounded-lg text-sm text-white hover:bg-white/10 transition-colors">
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Header & Menu -->
    <header class="md:hidden bg-primary text-white py-4 px-4 w-full flex justify-between items-center absolute top-0 left-0 z-50">
        <a href="/dashboard" class="font-bold text-lg"><?= escape_html($settings['nom']) ?></a>
        <button id="admin-mobile-btn" class="p-2 bg-white/10 rounded-md">Menu</button>
    </header>

    <!-- Mobile Menu Overlay -->
    <div id="admin-mobile-menu" class="hidden md:hidden fixed inset-0 bg-primary z-40 flex flex-col pt-20 px-4">
        <nav class="flex flex-col gap-4">
            <a href="/dashboard" class="px-4 py-3 bg-white/10 rounded-lg text-white font-semibold">Tableau de bord</a>
            <a href="/shipments" class="px-4 py-3 text-white/70 hover:text-white rounded-lg text-lg">Expéditions</a>
            <a href="/settings" class="px-4 py-3 text-white/70 hover:text-white rounded-lg text-lg">Paramètres</a>
        </nav>
        <form action="/logout" method="POST" class="mt-auto mb-8 mx-4">
            <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
            <button type="submit" class="w-full py-3 border border-white/20 rounded-lg text-white hover:bg-white/10">
                Déconnexion
            </button>
        </form>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col pt-16 md:pt-0 overflow-y-auto">
        <div class="p-6 lg:p-10 flex-1 max-w-7xl mx-auto w-full">
            <?php // Le contenu spécifique à la page sera injecté ici ?>
