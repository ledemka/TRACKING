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

            // Section 1 (D2) : Solutions Gallery (4 cartes services + Mosaïque de 5 tuiles)
            'solutions_gallery' => [
                'title' => 'Nos services logistiques',
                'subtitle' => 'Une gamme complète d\'opérations pour accompagner chaque phase du transport de colis.',
                'cards' => [
                    [
                        'id' => 'expedition',
                        'title' => 'Expédition',
                        'description' => 'Prise en charge initiale, étiquetage réglementaire et injection dans les flux d\'acheminement.',
                        'url' => '/services/expedition',
                        'icon' => 'inventory_2',
                    ],
                    [
                        'id' => 'suivi',
                        'title' => 'Suivi',
                        'description' => 'Consultation de l\'état d\'avancement, géolocalisation des étapes et horodatage des passages.',
                        'url' => '/services/suivi',
                        'icon' => 'track_changes',
                    ],
                    [
                        'id' => 'livraison',
                        'title' => 'Livraison',
                        'description' => 'Dernière étape du circuit logistique jusqu\'à la remise physique au destinataire final.',
                        'url' => '/services/livraison',
                        'icon' => 'local_shipping',
                    ],
                    [
                        'id' => 'gestion',
                        'title' => 'Gestion des expéditions',
                        'description' => 'Supervision coordonnée des volumes, contrôle des statuts et gestion des flux récurrents.',
                        'url' => '/services/gestion',
                        'icon' => 'settings_suggest',
                    ],
                ],
                'mosaic' => [
                    [
                        'title' => 'Expédition & Enregistrement',
                        'service_name' => 'Expédition',
                        'url' => '/services/expedition',
                        'image' => '/assets/img/site/service-expedition.webp',
                        'image_alt' => 'Préparation et fermeture d\'un carton d\'expédition avec ruban adhésif',
                        'width' => 900,
                        'height' => 672,
                        'grid_class' => 'col-span-12 sm:col-span-6 lg:col-span-4 h-72',
                    ],
                    [
                        'title' => 'Suivi & Contrôle optique',
                        'service_name' => 'Suivi',
                        'url' => '/services/suivi',
                        'image' => '/assets/img/site/service-suivi.webp',
                        'image_alt' => 'Lecture optique d\'un code-barres sur un colis dans une zone logistique',
                        'width' => 896,
                        'height' => 1200,
                        'grid_class' => 'col-span-12 sm:col-span-6 lg:col-span-4 h-72',
                    ],
                    [
                        'title' => 'Livraison au destinataire',
                        'service_name' => 'Livraison',
                        'url' => '/services/livraison',
                        'image' => '/assets/img/site/service-livraison.webp',
                        'image_alt' => 'Remise d\'un colis cartonné à l\'entrée d\'une résidence par un coursier de dos',
                        'width' => 900,
                        'height' => 900,
                        'grid_class' => 'col-span-12 sm:col-span-6 lg:col-span-4 h-72',
                    ],
                    [
                        'title' => 'Supervision & Entrepôt ordonné',
                        'service_name' => 'Gestion des expéditions',
                        'url' => '/services/gestion',
                        'image' => '/assets/img/site/service-gestion.webp',
                        'image_alt' => 'Vue en hauteur d\'un entrepôt logistique avec rayonnages ordonnés et allées de stockage',
                        'width' => 900,
                        'height' => 672,
                        'grid_class' => 'col-span-12 lg:col-span-7 h-80',
                    ],
                    [
                        'title' => 'Plateforme logistique centrale',
                        'service_name' => 'Expédition & Suivi',
                        'url' => '/services',
                        'image' => '/assets/img/site/section-qualite.webp',
                        'image_alt' => 'Vue extérieure d\'une plateforme de distribution logistique avec quais et camions au crépuscule',
                        'width' => 1376,
                        'height' => 768,
                        'grid_class' => 'col-span-12 lg:col-span-5 h-80',
                    ],
                ],
            ],

            // Section 2 (D2) : Section éditoriale et 4 blocs alternés
            'editorial' => [
                'title' => 'Une visibilité transparente sur le transport de colis',
                'paragraphs' => [
                    'L\'information logistique repose sur la clarté des jalons franchis par chaque envoi. Depuis le dépôt initial en centre de tri jusqu\'à sa destination, les enregistrements successifs permettent d\'identifier le parcours effectif du colis sans intermédiaire.',
                    'Notre plateforme rassemble ces points d\'étapes dans un format standardisé et consultable par référence, assurant la traçabilité des opérations de distribution pour l\'ensemble des intervenants.',
                ],
            ],

            'services_detail' => [
                [
                    'id' => 'expedition',
                    'title' => 'Expédition',
                    'tag' => 'Étape initiale',
                    'description' => 'L\'expédition formalise la prise en charge du colis dans le réseau de distribution. Chaque unité se voit attribuer un numéro d\'identification unique assurant la continuité de sa traçabilité.',
                    'items' => [
                        'Contrôle dimensionnel et pesée du colis au dépôt',
                        'Attribution d\'une étiquette normalisée avec référence de suivi',
                        'Enregistrement de l\'adresse d\'origine et des coordonnées du destinataire',
                        'Transmission des informations dans la base de suivi',
                    ],
                    'url' => '/services/expedition',
                    'image' => '/assets/img/site/service-expedition.webp',
                    'image_alt' => 'Préparation et fermeture d\'un carton d\'expédition avec ruban adhésif',
                    'width' => 900,
                    'height' => 672,
                    'reverse' => false,
                ],
                [
                    'id' => 'suivi',
                    'title' => 'Suivi',
                    'tag' => 'Traçabilité en continu',
                    'description' => 'Le suivi documente le parcours géographique du colis à chaque passage en centre de transit. Les événements horodatés informent de l\'état courant au fil des étapes du circuit.',
                    'items' => [
                        'Consultation directe par numéro d\'envoi sur interface web',
                        'Historique chronologique des passages aux points de tri',
                        'Représentation cartographique des points franchis',
                        'Mise à jour des statuts réglementaires d\'acheminement',
                    ],
                    'url' => '/services/suivi',
                    'image' => '/assets/img/site/service-suivi.webp',
                    'image_alt' => 'Lecture optique d\'un code-barres sur un colis dans une zone logistique',
                    'width' => 896,
                    'height' => 1200,
                    'reverse' => true,
                ],
                [
                    'id' => 'livraison',
                    'title' => 'Livraison',
                    'tag' => 'Étape finale',
                    'description' => 'La livraison constitue le dernier maillon de l\'acheminement, consistant en la remise en main propre ou en point de distribution convenu avec enregistrement de clôture.',
                    'items' => [
                        'Prise en charge par le circuit de distribution locale',
                        'Mise à jour du statut lors de la tournée finale',
                        'Confirmation factuelle de la remise au destinataire',
                        'Clôture du cycle de suivi de l\'envoi',
                    ],
                    'url' => '/services/livraison',
                    'image' => '/assets/img/site/service-livraison.webp',
                    'image_alt' => 'Remise d\'un colis cartonné à l\'entrée d\'une résidence par un coursier de dos',
                    'width' => 900,
                    'height' => 900,
                    'reverse' => false,
                ],
                [
                    'id' => 'gestion',
                    'title' => 'Gestion des expéditions',
                    'tag' => 'Supervision des flux',
                    'description' => 'La gestion des expéditions permet une vue synthétique sur l\'ensemble des colis enregistrés, facilitant le suivi des volumes et la vérification des états de distribution.',
                    'items' => [
                        'Vue centralisée de l\'état des envois enregistrés',
                        'Filtres de recherche par numéro, destinataire et ville',
                        'Consultation des journaux d\'événements associés aux colis',
                        'Uniformisation des pratiques de renseignement logistique',
                    ],
                    'url' => '/services/gestion',
                    'image' => '/assets/img/site/service-gestion.webp',
                    'image_alt' => 'Vue en hauteur d\'un entrepôt logistique avec rayonnages ordonnés et allées de stockage',
                    'width' => 900,
                    'height' => 672,
                    'reverse' => true,
                ],
            ],

            // Section 3 (D2) : Quality Tabs (Un suivi clair à chaque étape)
            'quality_tabs' => [
                'title' => 'Un suivi clair à chaque étape',
                'subtitle' => 'Découvrez les modalités de traitement pour chacune des quatre phases de notre circuit logistique.',
                'image_bg' => '/assets/img/site/contact-quai.webp',
                'image_bg_alt' => 'Quais de chargement et véhicules de transport alignés sous la lumière du coucher de soleil',
                'width' => 1376,
                'height' => 768,
                'tabs' => [
                    [
                        'id' => 'tab-expedition',
                        'title' => 'Expédition',
                        'badge' => 'Phase 1',
                        'heading' => 'Enregistrement initial et conditionnement',
                        'description' => 'Chaque colis confié au réseau logistique fait l\'objet d\'une pesée, d\'une vérification d\'emballage et de l\'émission d\'une étiquette à code-barres. Ces données initiales établissent la fiche de suivi permanente de l\'expédition.',
                        'points' => [
                            'Fiche d\'envoi créée dès la réception en quai de départ',
                            'Génération immédiate du numéro d\'identification unique',
                            'Statut d\'entrée initial conforme au cycle d\'acheminement',
                        ],
                        'url' => '/services/expedition',
                        'link_label' => 'Consulter les détails d\'expédition',
                    ],
                    [
                        'id' => 'tab-suivi',
                        'title' => 'Suivi',
                        'badge' => 'Phase 2',
                        'heading' => 'Traçabilité des passages en plateforme',
                        'description' => 'Aux nœuds de correspondance et centres de tri, chaque colis est scanné pour consigner son heure de passage et sa position. Les usagers peuvent ainsi constater les avancées concrètes de leur transport.',
                        'points' => [
                            'Lecture automatisée sur chaînes de convoyage et de tri',
                            'Renseignements géographiques des étapes successives',
                            'Accès public libre sans obligation de création de compte',
                        ],
                        'url' => '/services/suivi',
                        'link_label' => 'Consulter les modalités de suivi',
                    ],
                    [
                        'id' => 'tab-livraison',
                        'title' => 'Livraison',
                        'badge' => 'Phase 3',
                        'heading' => 'Acheminement terminal et constat de remise',
                        'description' => 'Le colis est confié à la tournée de distribution finale. Une fois parvenu à l\'adresse de destination, la confirmation est enregistrée et le cycle d\'acheminement prend fin.',
                        'points' => [
                            'Prise en charge pour la distribution de proximité',
                            'Constat horodaté lors de la remise au destinataire',
                            'Mise à jour immédiate du statut final de livraison',
                        ],
                        'url' => '/services/livraison',
                        'link_label' => 'Consulter les critères de livraison',
                    ],
                    [
                        'id' => 'tab-gestion',
                        'title' => 'Gestion',
                        'badge' => 'Phase 4',
                        'heading' => 'Supervision coordonnée des flux de colis',
                        'description' => 'Pour les structures expédiant de multiples colis, la vue d\'ensemble facilite le repérage des envois nécessitant une attention spécifique et l\'organisation des opérations.',
                        'points' => [
                            'Tableau de synthèse des statuts de tous les envois',
                            'Recherche ciblée par nom, référence ou localité',
                            'Traçabilité complète des interventions sur chaque fiche',
                        ],
                        'url' => '/services/gestion',
                        'link_label' => 'Consulter la gestion des expéditions',
                    ],
                ],
            ],

            // Section 4 (D2) : Contact CTA
            'contact_cta' => [
                'title' => 'Une question ? Contactez-nous',
                'description' => 'Pour toute demande relative au fonctionnement de la plateforme ou aux informations de suivi, notre équipe est à votre disposition.',
                'btn_text' => 'Accéder au formulaire de contact',
                'btn_url' => '/contact',
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
                ],
                'legal_notice' => '[À compléter]',
                'privacy_notice' => '[À compléter]',
            ],
        ];
    }
}
