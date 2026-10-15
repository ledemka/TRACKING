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
            <a href="/dashboard" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/dashboard' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' ?> rounded-lg font-semibold transition-colors"><?= escape_html(__('nav.dashboard')) ?></a>
            <a href="/shipments" class="px-4 py-3 <?= strpos($_SERVER['REQUEST_URI'], '/shipments') === 0 ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' ?> rounded-lg font-semibold transition-colors"><?= escape_html(__('nav.shipments')) ?></a>
            <a href="/customers" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/customers' ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' ?> rounded-lg font-semibold transition-colors"><?= escape_html(__('nav.customers')) ?></a>
        </nav>
        <div class="p-4 mt-auto space-y-3">
            <div class="flex items-center justify-between px-2">
                <span class="text-xs text-white/70"><?= escape_html(__('common.language')) ?></span>
                <?= render_language_switcher('dark') ?>
            </div>
            <div class="flex items-center justify-center gap-2">
                <div class="w-8 h-8 rounded-full bg-[#FFE500] text-[#171717] font-extrabold text-xs flex items-center justify-center tracking-tight shadow-sm select-none" title="<?= __attr('common.admin') ?>">
                    <?= escape_html($admin_initials) ?>
                </div>
            </div>
            <form action="/logout" method="POST" class="w-full">
                <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
                <button type="submit" class="flex w-full items-center justify-center px-4 py-2 border border-white/20 rounded-lg text-sm text-white hover:bg-white/10 transition-colors">
                    <?= escape_html(__('auth.logout')) ?>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Header & Menu -->
    <header class="md:hidden bg-primary text-white py-4 px-4 w-full flex justify-between items-center sticky top-0 left-0 z-[1050]" style="position: sticky; top: 0; z-index: 1050;">
        <a href="/dashboard" class="font-bold text-lg flex items-center gap-2">
            <img src="/assets/img/logo/logo-mark-fond-sombre.svg" alt="Logo" class="h-6 w-6 shrink-0" width="24" height="24">
            <span><?= escape_html($settings['nom']) ?></span>
        </a>
        <div class="flex items-center gap-2 sm:gap-3">
            <?= render_language_switcher('dark') ?>
            <div class="w-8 h-8 rounded-full bg-[#FFE500] text-[#171717] font-extrabold text-xs flex items-center justify-center tracking-tight shadow-sm select-none" title="<?= __attr('common.admin') ?>">
                <?= escape_html($admin_initials) ?>
            </div>
            <button id="admin-mobile-btn" class="p-2 bg-white/10 rounded-md">Menu</button>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div id="admin-mobile-menu" class="hidden md:hidden fixed inset-0 bg-primary z-40 flex flex-col pt-20 px-4">
        <nav class="flex flex-col gap-4">
            <a href="/dashboard" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/dashboard' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white' ?> rounded-lg text-lg font-semibold"><?= escape_html(__('nav.dashboard')) ?></a>
            <a href="/shipments" class="px-4 py-3 <?= strpos($_SERVER['REQUEST_URI'], '/shipments') === 0 ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white' ?> rounded-lg text-lg font-semibold"><?= escape_html(__('nav.shipments')) ?></a>
            <a href="/customers" class="px-4 py-3 <?= $_SERVER['REQUEST_URI'] == '/customers' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white' ?> rounded-lg text-lg font-semibold"><?= escape_html(__('nav.customers')) ?></a>
        </nav>
        <form action="/logout" method="POST" class="mt-auto mb-8 mx-4">
            <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
            <button type="submit" class="w-full py-3 border border-white/20 rounded-lg text-white hover:bg-white/10">
                <?= escape_html(__('auth.logout')) ?>
            </button>
        </form>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col pt-16 md:pt-0 overflow-y-auto">
        <div class="p-6 lg:p-10 flex-1 max-w-7xl mx-auto w-full">
            <?php // Le contenu spécifique à la page sera injecté ici ?>
