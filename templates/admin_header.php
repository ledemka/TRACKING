<?php
require_admin();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
$csrf_token = $_SESSION['csrf_token'] ?? '';

global $pdo;
$stmt_admin_email = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt_admin_email->execute([$_SESSION['user_id']]);
$current_admin_email = $stmt_admin_email->fetchColumn() ?: '';
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
<body class="bg-surface font-body-md text-on-surface flex flex-col min-h-screen">
    <header class="fixed top-0 w-full z-50 bg-primary text-white shadow-md">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/dashboard" class="flex items-center gap-2.5 text-white hover:opacity-90 transition-opacity">
                    <img src="/assets/img/logo/logo-mark-fond-sombre.svg" alt="Logo" class="h-8 w-8 shrink-0" width="32" height="32">
                    <span class="font-title-md font-bold tracking-tight">Administration</span>
                </a>
                <nav class="hidden md:flex gap-4 ml-8">
                    <a href="/dashboard" class="hover:text-accent">Dashboard</a>
                    <a href="/shipments" class="hover:text-accent">Colis</a>
                    <a href="/settings" class="hover:text-accent">Paramètres</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm"><?= escape_html($current_admin_email) ?></span>
                <form action="/logout" method="POST" class="inline m-0 p-0">
                    <input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
                    <button type="submit" class="text-sm hover:text-accent flex items-center bg-transparent border-none cursor-pointer text-white">
                        <span class="material-symbols-outlined">logout</span>
                    </button>
                </form>
            </div>
        </div>
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
