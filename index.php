<?php
// index.php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/templates/site_content.php';

$page_title = 'Accueil — Suivi et acheminement de colis en temps réel';
$page_description = "Plateforme de suivi des expéditions de colis : consultation en direct de l'avancement, de la localisation et des étapes d'acheminement.";
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col">
    <?php 
    // Étape D1 : socle + haut de page
    require __DIR__ . '/templates/home/hero_carousel.php';
    require __DIR__ . '/templates/home/transition_banner.php';
    require __DIR__ . '/templates/home/shipment_tracker.php';
    require __DIR__ . '/templates/home/company_intro.php';

    // Étape D2 : milieu de page (Mosaïque, Services, Onglets & Appel à l'action)
    require __DIR__ . '/templates/home/solutions_gallery.php';
    require __DIR__ . '/templates/home/services_section.php';
    require __DIR__ . '/templates/home/quality_tabs.php';
    require __DIR__ . '/templates/home/contact_cta.php';
    ?>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
