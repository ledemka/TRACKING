<?php
// templates/site_content.php
// Source unique de contenu textuel et structurel du site public
if (!function_exists('get_site_content')) {
    function get_site_content() {
        global $settings;
        if (!isset($settings) || empty($settings)) {
            $settings = get_app_settings();
        }

        $email = !empty($settings['email']) ? $settings['email'] : '[À compléter]';
        $telephone = !empty($settings['telephone']) ? $settings['telephone'] : '[À compléter]';
        $adresse = !empty($settings['adresse']) ? $settings['adresse'] : '[À compléter]';
        $nom = !empty($settings['nom']) ? $settings['nom'] : 'Plateforme de Suivi';

        return [
            'brand' => [
                'nom' => $nom,
                'description' => 'Plateforme de suivi des expéditions et consultation des étapes d\'acheminement.',
            ],
            'contact' => [
                'email' => $email,
                'telephone' => $telephone,
                'adresse' => $adresse,
            ],
            'nav' => [
                ['label' => 'Accueil', 'url' => '/'],
                ['label' => 'Services', 'url' => '/services'],
                ['label' => 'Contact', 'url' => '/contact'],
            ],
            'hero_slides' => [
                [
                    'id' => 1,
                    'title' => 'Suivi rigoureux et visibilité complète',
                    'subtitle' => 'Consultez le déroulement de chaque acheminement à tout moment avec votre référence.',
                    'image' => '/assets/img/site/hero-1-entrepot.webp',
                    'image_alt' => 'Intérieur d\'un entrepôt de stockage logistique avec allées de rayonnages et chariot élévateur',
                    'width' => 1376,
                    'height' => 768,
                    'btn_text' => 'Suivre un colis',
                    'btn_url' => '/track',
                ],
                [
                    'id' => 2,
                    'title' => 'Acheminement coordonné vers la destination',
                    'subtitle' => 'Un suivi étape par étape depuis la prise en charge jusqu\'au point de remise.',
                    'image' => '/assets/img/site/hero-2-livraison.webp',
                    'image_alt' => 'Véhicule utilitaire blanc circulant sur une voie urbaine à l\'aube',
                    'width' => 1376,
                    'height' => 768,
                    'btn_text' => 'Découvrir nos services',
                    'btn_url' => '/services',
                ],
                [
                    'id' => 3,
                    'title' => 'Organisation méthodique et étapes identifiées',
                    'subtitle' => 'Chaque colis est enregistré et localisé aux points clés de transit logistique.',
                    'image' => '/assets/img/site/hero-3-tri.webp',
                    'image_alt' => 'Lignes automatisées de convoyage et de tri de cartons dans un centre logistique',
                    'width' => 1376,
                    'height' => 768,
                    'btn_text' => 'Nous contacter',
                    'btn_url' => '/contact',
                ],
            ],
            'transition_banner' => [
                'text' => 'Une interface dédiée à la consultation claire et directe de vos colis.',
            ],
            'tracker_block' => [
                'title' => 'Rechercher un envoi',
                'description' => 'Saisissez votre numéro de référence pour accéder au statut actuel et à l\'historique des points de passage.',
                'placeholder' => 'Ex : COLIS-2026-00125',
                'btn_text' => 'Consulter le suivi',
                'image' => '/assets/img/site/intro-scan.webp',
                'image_alt' => 'Mains gantées scannant l\'étiquette d\'un colis avec un terminal optique portable',
                'width' => 900,
                'height' => 672,
            ],
            'intro' => [
                'title' => 'Une gestion structurée des flux de colis',
                'paragraphs' => [
                    'Notre outil permet de suivre l\'avancement des colis à travers un réseau d\'acheminement organisé. Chaque mouvement fait l\'objet d\'une mise à jour horodatée.',
                    'Du dépôt initial à la remise finale, les expéditeurs et destinataires accèdent aux informations factuelles relatives à chaque étape du parcours.',
                ],
                'image' => '/assets/img/site/intro-scan.webp',
                'image_alt' => 'Mains gantées scannant l\'étiquette d\'un colis avec un terminal optique portable',
                'width' => 900,
                'height' => 672,
            ],
            'features' => [
                [
                    'title' => 'Suivi simple',
                    'description' => 'Un identifiant unique pour accéder immédiatement aux données de l\'envoi sans démarche superflue.',
                    'link_text' => 'Contacter le support',
                    'link_url' => '/contact',
                    'icon' => 'search',
                ],
                [
                    'title' => 'Statuts clairs',
                    'description' => 'Quatre statuts précis : Expédié, En cours de livraison, Livré, Retardé pour une compréhension sans ambiguïté.',
                    'link_text' => 'En savoir plus',
                    'link_url' => '/contact',
                    'icon' => 'check_circle',
                ],
                [
                    'title' => 'Informations à jour',
                    'description' => 'Enregistrement régulier des passages en plateforme logistique et actualisation des points d\'étape.',
                    'link_text' => 'Poser une question',
                    'link_url' => '/contact',
                    'icon' => 'schedule',
                ],
            ],
            'services' => [
                [
                    'id' => 'expedition',
                    'slug' => 'expedition',
                    'title' => 'Expédition',
                    'description' => 'Prise en charge initiale, étiquetage des unités et enregistrement dans la plateforme.',
                    'url' => '/services/expedition',
                ],
                [
                    'id' => 'suivi',
                    'slug' => 'suivi',
                    'title' => 'Suivi',
                    'description' => 'Consultation en ligne de l\'historique des étapes et de l\'état d\'avancement de l\'envoi.',
                    'url' => '/services/suivi',
                ],
                [
                    'id' => 'livraison',
                    'slug' => 'livraison',
                    'title' => 'Livraison',
                    'description' => 'Acheminement final et confirmation de la réception du colis.',
                    'url' => '/services/livraison',
                ],
                [
                    'id' => 'gestion',
                    'slug' => 'gestion',
                    'title' => 'Gestion des expéditions',
                    'description' => 'Organisation d\'ensemble des envois et centralisation des informations de transport.',
                    'url' => '/services/gestion',
                ],
            ],
            'footer' => [
                'quick_links' => [
                    ['label' => 'Accueil', 'url' => '/'],
                    ['label' => 'Services', 'url' => '/services'],
                    ['label' => 'Suivre un colis', 'url' => '/track'],
                    ['label' => 'Contact', 'url' => '/contact'],
                    ['label' => 'Mentions légales', 'url' => '/mentions-legales'],
                    ['label' => 'Confidentialité', 'url' => '/confidentialite'],
                ],
                'legal_notice' => '[À compléter]',
                'privacy_notice' => '[À compléter]',
            ],
        ];
    }
}
