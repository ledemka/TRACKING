<?php
// index.php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/templates/site_content.php';

$page_title = 'Accueil';
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col">
    <?php 
    // Étape D1 : socle + haut de page
    require __DIR__ . '/templates/home/hero_carousel.php';
    require __DIR__ . '/templates/home/transition_banner.php';
    require __DIR__ . '/templates/home/shipment_tracker.php';
    require __DIR__ . '/templates/home/company_intro.php';
    require __DIR__ . '/templates/home/feature_cards.php';
    ?>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
