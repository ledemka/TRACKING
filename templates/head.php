<?php
require_once __DIR__ . '/../api/bootstrap.php';
$settings = get_app_settings();

// Validation stricte des couleurs (fallback si invalide)
$colorPrimary = preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $settings['color_primary']) ? $settings['color_primary'] : '#0B1F3A';
$colorAccent = preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $settings['color_accent']) ? $settings['color_accent'] : '#F59E0B';
?>
<!DOCTYPE html>
<html lang="<?= get_current_lang() ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171717">
    <title><?= escape_html($settings['nom']) ?> — <?= escape_html($page_title ?? __('nav.track')) ?></title>
    
    <?php
    $defaultDesc = __('footer.company_desc');
    $metaDesc = !empty($page_description) ? $page_description : $defaultDesc;
    $ogTitle = (!empty($page_title) ? $page_title . ' — ' : '') . $settings['nom'];

    
    // URL canonique et métadonnées Open Graph
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $baseUrl = function_exists('get_base_url') ? get_base_url() : 'https://pava-shipping.fr';
    $ogUrl = $baseUrl . $currentPath;
    $ogImage = $baseUrl . '/assets/img/logo-mark-fond-sombre.png';
    ?>
    <meta name="description" content="<?= escape_html($metaDesc) ?>">
    
    <!-- Open Graph / Réseaux sociaux -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape_html($settings['nom']) ?>">
    <meta property="og:title" content="<?= escape_html($ogTitle) ?>">
    <meta property="og:description" content="<?= escape_html($metaDesc) ?>">
    <meta property="og:url" content="<?= escape_html($ogUrl) ?>">
    <meta property="og:image" content="<?= escape_html($ogImage) ?>">
    <!-- Balises d'internationalisation et référencement multilingue (SEO) -->
    <link rel="canonical" href="<?= escape_html(get_canonical_url(true)) ?>">
    <?php foreach (SUPPORTED_LANGUAGES as $lCode): ?>
    <link rel="alternate" hreflang="<?= $lCode ?>" href="<?= escape_html(get_localized_url($lCode, true)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= escape_html(get_canonical_url(true)) ?>">
    
    <link rel="icon" type="image/svg+xml" href="/assets/img/logo/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/img/logo/apple-touch-icon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: <?= $colorPrimary ?>;
            --color-accent: <?= $colorAccent ?>;
        }
    </style>
</head>
<body class="bg-surface min-h-screen flex flex-col font-sans">
