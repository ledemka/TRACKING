<?php
require_admin();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? escape_html($page_title) . ' - ' : '' ?>Admin</title>
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <?php if (isset($use_map) && $use_map): ?>
    <link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css">
    <script src="/assets/vendor/leaflet/leaflet.js"></script>
    <script>
        window.MAP_TILE_URL = <?= json_encode(getenv('MAP_TILE_URL')) ?>;
        window.MAP_MAX_ZOOM = <?= json_encode(getenv('MAP_MAX_ZOOM')) ?>;
        window.MAP_ATTRIBUTION = <?= json_encode(getenv('MAP_ATTRIBUTION')) ?>;
    </script>
    <?php endif; ?>
</head>
<body class="bg-surface font-body-md text-on-surface flex flex-col min-h-screen">
    <header class="fixed top-0 w-full z-50 bg-primary text-white shadow-md">
        <div class="h-16 px-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="font-title-md font-bold">Administration</span>
                <nav class="hidden md:flex gap-4 ml-8">
                    <a href="/dashboard" class="hover:text-accent">Dashboard</a>
                    <a href="/shipments" class="hover:text-accent">Colis</a>
                    <a href="/settings" class="hover:text-accent">Paramètres</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm"><?= escape_html($_SESSION['admin_email'] ?? '') ?></span>
                <a href="/logout" class="text-sm hover:text-accent flex items-center"><span class="material-symbols-outlined">logout</span></a>
            </div>
        </div>
    </header>
    <main class="flex-grow pt-20 px-4 pb-8 max-w-7xl mx-auto w-full">
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
