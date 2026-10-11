<?php 
$page_title = 'Administration';
require_once __DIR__ . '/head.php'; 

global $pdo;
$stmt_admin_email = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt_admin_email->execute([$_SESSION['user_id']]);
$current_admin_email = $stmt_admin_email->fetchColumn() ?: '';
// Calcul des initiales
$admin_initials = '';
if (!empty($current_admin_email)) {
    $emailPrefix = explode('@', $current_admin_email)[0];
    $admin_initials = strtoupper(mb_substr($emailPrefix, 0, 2));
}
if ($admin_initials === '') {
    $admin_initials = 'AD';
}
?>
    <!-- Sidebar Desktop -->
    <aside class="hidden md:flex flex-col w-64 bg-primary text-white flex-shrink-0 min-h-screen">
        <div class="p-6">
            <a href="/dashboard" class="font-bold text-xl tracking-tight flex items-center gap-2.5">
                <img src="/assets/img/logo/logo-mark-fond-sombre.svg" alt="Logo" class="h-8 w-8 shrink-0" width="32" height="32">
                <span><?= escape_html($settings['nom']) ?></span>
            </a>
        </div>
        <nav class="flex-1 mt-6 flex flex-col gap-2 px-4">
            <a href="/dashboard" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/dashboard' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' ?> rounded-lg font-semibold transition-colors">Tableau de bord</a>
            <a href="/shipments" class="px-4 py-3 <?= strpos($_SERVER['REQUEST_URI'], '/shipments') === 0 ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' ?> rounded-lg font-semibold transition-colors">Expéditions</a>
            <a href="/customers" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/customers' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' ?> rounded-lg font-semibold transition-colors">Clients</a>
        </nav>
        <div class="p-4 mt-auto">
            <div class="mb-4 flex items-center justify-center gap-2">
                <div class="w-8 h-8 rounded-full bg-[#FFE500] text-[#171717] font-extrabold text-xs flex items-center justify-center tracking-tight shadow-sm select-none" title="Administrateur">
                    <?= escape_html($admin_initials) ?>
                </div>
            </div>
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
        <a href="/dashboard" class="font-bold text-lg flex items-center gap-2">
            <img src="/assets/img/logo/logo-mark-fond-sombre.svg" alt="Logo" class="h-6 w-6 shrink-0" width="24" height="24">
            <span><?= escape_html($settings['nom']) ?></span>
        </a>
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#FFE500] text-[#171717] font-extrabold text-xs flex items-center justify-center tracking-tight shadow-sm select-none" title="Administrateur">
                <?= escape_html($admin_initials) ?>
            </div>
            <button id="admin-mobile-btn" class="p-2 bg-white/10 rounded-md">Menu</button>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div id="admin-mobile-menu" class="hidden md:hidden fixed inset-0 bg-primary z-40 flex flex-col pt-20 px-4">
        <nav class="flex flex-col gap-4">
            <a href="/dashboard" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/dashboard' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white' ?> rounded-lg text-lg font-semibold">Tableau de bord</a>
            <a href="/shipments" class="px-4 py-3 <?= strpos($_SERVER['REQUEST_URI'], '/shipments') === 0 ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white' ?> rounded-lg text-lg font-semibold">Expéditions</a>
            <a href="/customers" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/customers' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white' ?> rounded-lg text-lg font-semibold">Clients</a>
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
