<?php
require_admin();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
$csrf_token = $_SESSION['csrf_token'] ?? '';

global $pdo;
$stmt_admin = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_admin->execute([$_SESSION['user_id']]);
$current_admin_user = $stmt_admin->fetch() ?: ['name' => 'Admin', 'email' => ''];
$current_admin_email = $current_admin_user['email'] ?? '';
$current_admin_name = $current_admin_user['name'] ?? '';

// Calcul des initiales (ex: "Roger Kamdem" -> "RK", "admin@example.com" -> "AD")
$admin_initials = '';
if (!empty($current_admin_name) && strtolower($current_admin_name) !== 'admin') {
    $parts = preg_split('/\s+/', trim($current_admin_name));
    if (count($parts) >= 2) {
        $admin_initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    } else {
        $admin_initials = strtoupper(mb_substr($current_admin_name, 0, 2));
    }
}
if ($admin_initials === '' && !empty($current_admin_email)) {
    $emailPrefix = explode('@', $current_admin_email)[0];
    $admin_initials = strtoupper(mb_substr($emailPrefix, 0, 2));
}
if ($admin_initials === '') {
    $admin_initials = 'AD';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? escape_html($page_title) . ' - ' : '' ?>Admin</title>
    <link rel="icon" type="image/svg+xml" href="/assets/img/logo/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/img/logo/apple-touch-icon.png">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <?php if (isset($use_map) && $use_map): ?>
    <link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">
    <script src="/assets/vendor/leaflet/leaflet.js"></script>
    <script>
        window.MAP_TILE_URL = <?= json_encode(getenv('MAP_TILE_URL')) ?>;
        window.MAP_MAX_ZOOM = <?= json_encode(getenv('MAP_MAX_ZOOM')) ?>;
        window.MAP_ATTRIBUTION = <?= json_encode(getenv('MAP_ATTRIBUTION')) ?>;
        window.HERE_BROWSER_API_KEY = <?= json_encode(getenv('HERE_BROWSER_API_KEY')) ?>;
    </script>
    <?php endif; ?>
</head>
<?php
$currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<body class="bg-surface font-body-md text-on-surface flex flex-col min-h-screen">
    <!-- Header Admin : Thème Sombre Raffiné #171717 avec liseré jaune #FFE500 -->
    <header class="fixed top-0 w-full z-50 bg-[#171717] text-white border-b-2 border-[#FFE500] shadow-md transition-all">
        <div class="h-16 max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4">
            <!-- Zone Gauche : Burger + Logo + Nav Desktop -->
            <div class="flex items-center gap-3 sm:gap-6">
                <!-- Bouton Burger Mobile Stylisé -->
                <button id="admin-burger-btn" 
                        type="button" 
                        class="md:hidden p-2 rounded-lg text-slate-200 hover:text-white hover:bg-white/10 active:bg-white/15 transition-all flex items-center justify-center focus-brand-dark" 
                        aria-label="Ouvrir le menu de navigation" 
                        aria-expanded="false" 
                        aria-controls="admin-mobile-menu">
                    <span class="material-symbols-outlined text-2xl select-none" id="admin-burger-icon">menu</span>
                </button>

                <!-- Logo & Titre Admin -->
                <a href="/dashboard" class="flex items-center gap-2.5 text-white hover:opacity-90 transition-opacity focus-brand-dark rounded-md px-1 py-0.5">
                    <img src="/assets/img/logo/logo-mark-fond-sombre.svg" alt="Logo" class="h-8 w-auto object-contain shrink-0" width="32" height="32">
                    <div class="flex items-baseline gap-1.5">
                        <span class="font-bold text-lg sm:text-xl tracking-tight text-white">TRACKING</span>
                        <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-white/10 text-[#FFE500] tracking-wider border border-white/10 hidden sm:inline-block">Admin</span>
                    </div>
                </a>
                
                <!-- Navigation Desktop avec repères actifs -->
                <nav class="hidden md:flex items-center gap-1 ml-4" aria-label="Navigation principale admin">
                    <?php 
                    $navLinks = [
                        ['url' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                        ['url' => '/shipments', 'label' => 'Colis', 'icon' => 'package_2'],
                    ];
                    foreach ($navLinks as $nl):
                        $isActive = ($currentRoute === $nl['url'] || ($nl['url'] === '/shipments' && str_starts_with($currentRoute, '/shipments')));
                    ?>
                        <a href="<?= $nl['url'] ?>" 
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition-all focus-brand-dark <?= $isActive ? 'bg-white/15 text-[#FFE500]' : 'text-slate-300 hover:text-white hover:bg-white/5' ?>">
                            <span class="material-symbols-outlined text-base"><?= $nl['icon'] ?></span>
                            <span><?= $nl['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            
            <!-- Zone Droite : Badge utilisateur + Action Voir Site + Déconnexion -->
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="/" target="_blank" rel="noopener noreferrer" 
                   class="hidden lg:inline-flex items-center gap-1.5 text-xs text-slate-300 hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-white/10 transition-colors focus-brand-dark" 
                   title="Ouvrir le site public dans un nouvel onglet">
                    <span class="material-symbols-outlined text-sm text-[#FFE500]">open_in_new</span>
                    <span>Site public</span>
                </a>

                <!-- Pastille profil admin avec initiales -->
                <div class="flex items-center gap-1.5 sm:gap-2 bg-white/5 border border-white/10 rounded-full p-1">
                    <div class="w-8 h-8 rounded-full bg-[#FFE500] text-[#171717] font-extrabold text-xs flex items-center justify-center tracking-tight shadow-sm select-none" 
                         title="Administrateur (<?= escape_html($admin_initials) ?>)" 
                         aria-label="Administrateur : <?= escape_html($admin_initials) ?>">
                        <?= escape_html($admin_initials) ?>
                    </div>
                    <form action="/logout" method="POST" class="inline m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                        <button type="submit" 
                                class="w-8 h-8 rounded-full bg-white/10 hover:bg-red-600/90 text-slate-300 hover:text-white flex items-center justify-center transition-all focus-brand-dark border-none cursor-pointer" 
                                title="Se déconnecter"
                                aria-label="Se déconnecter">
                            <span class="material-symbols-outlined text-base">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Menu Mobile Déroulant — Esthétique Noire & Jaune #171717 / #FFE500 -->
        <nav id="admin-mobile-menu" 
             class="hidden md:hidden bg-[#171717] border-t border-white/10 px-4 py-3 space-y-1 shadow-2xl animate-fade-in" 
             aria-label="Navigation mobile">
            <?php 
            foreach ($navLinks as $nl):
                $isActive = ($currentRoute === $nl['url'] || ($nl['url'] === '/shipments' && str_starts_with($currentRoute, '/shipments')));
            ?>
                <a href="<?= $nl['url'] ?>" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold transition-all <?= $isActive ? 'bg-[#FFE500] text-[#171717]' : 'text-slate-200 hover:bg-white/10 hover:text-white' ?>">
                    <span class="material-symbols-outlined text-xl"><?= $nl['icon'] ?></span>
                    <span><?= $nl['label'] ?></span>
                </a>
            <?php endforeach; ?>
            
            <div class="pt-2 border-t border-white/10 mt-2">
                <a href="/" target="_blank" rel="noopener noreferrer" 
                   class="flex items-center justify-between px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-[#FFE500]">open_in_new</span>
                        <span>Voir le site public</span>
                    </span>
                    <span class="text-[10px] bg-white/10 px-2 py-0.5 rounded text-slate-300">Ouvrir</span>
                </a>
            </div>
        </nav>
    </header>
    <main class="flex-grow pt-20 px-4 pb-8 max-w-7xl mx-auto w-full relative z-0">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-status-delivered-bg border border-green-500 text-status-delivered-text p-4 rounded mb-6" role="status">
                <?= escape_html($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 border border-red-500 text-red-700 p-4 rounded mb-6" role="alert">
                <?= escape_html($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
