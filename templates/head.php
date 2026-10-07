<?php
require_once __DIR__ . '/../api/bootstrap.php';
$settings = get_app_settings();

// Validation stricte des couleurs (fallback si invalide)
$colorPrimary = preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $settings['color_primary']) ? $settings['color_primary'] : '#0B1F3A';
$colorAccent = preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $settings['color_accent']) ? $settings['color_accent'] : '#F59E0B';
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($settings['nom']) ?> - <?= escape_html($page_title ?? 'Suivi de colis') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        :root {
            --color-primary: <?= $colorPrimary ?>;
            --color-accent: <?= $colorAccent ?>;
        }
    </style>
</head>
<body class="bg-surface min-h-screen flex flex-col font-sans">
