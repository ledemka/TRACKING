<?php
// templates/site_content.php
// Source unique de contenu textuel et structurel du site public, 100% internationalisé
if (!function_exists('get_site_content')) {
    function get_site_content() {
        global $settings;
        if (!isset($settings) || empty($settings)) {
            $settings = get_app_settings();
        }

        $email = !empty($settings['email']) ? $settings['email'] : 'contact@pava-shipping.fr';
        $telephone = !empty($settings['telephone']) ? $settings['telephone'] : '0032465297645';
        $adresse = !empty($settings['adresse']) ? $settings['adresse'] : 'Fluitbergstraat 59, 2900 Schoten';
        $nom = !empty($settings['nom']) ? $settings['nom'] : 'Plateforme de Suivi';

        return [
            'brand' => [
                'nom' => $nom,
                'description' => __('footer.company_desc'),
            ],
            'contact' => [
                'email' => $email,
                'telephone' => $telephone,
                'adresse' => $adresse,
            ],
            'nav' => [
                ['label' => __('nav.home'), 'url' => '/'],
                ['label' => __('nav.services'), 'url' => '/services'],
                ['label' => __('nav.contact'), 'url' => '/contact'],
            ],
            'hero_slides' => [
                [
                    'id' => 1,
                    'title' => __('home.hero_title_1'),
                    'subtitle' => __('home.hero_sub_1'),
                    'image' => '/assets/img/site/hero-1-entrepot.webp',
                    'image_alt' => __('home.hero_alt_1'),
                    'width' => 1376,
                    'height' => 768,
                    'btn_text' => __('home.hero_btn_1'),
                    'btn_url' => '/track',
                ],
                [
                    'id' => 2,
                    'title' => __('home.hero_title_2'),
                    'subtitle' => __('home.hero_sub_2'),
                    'image' => '/assets/img/site/hero-2-livraison.webp',
                    'image_alt' => __('home.hero_alt_2'),
                    'width' => 1376,
                    'height' => 768,
                    'btn_text' => __('home.hero_btn_2'),
                    'btn_url' => '/services',
                ],
                [
                    'id' => 3,
                    'title' => __('home.hero_title_3'),
                    'subtitle' => __('home.hero_sub_3'),
                    'image' => '/assets/img/site/hero-3-tri.webp',
                    'image_alt' => __('home.hero_alt_3'),
                    'width' => 1376,
                    'height' => 768,
                    'btn_text' => __('home.hero_btn_3'),
                    'btn_url' => '/contact',
                ],
            ],
            'transition_banner' => [
                'text' => __('home.transition_banner'),
            ],
            'tracker_block' => [
                'title' => __('home.tracker_title'),
                'description' => __('home.tracker_desc'),
                'placeholder' => __('home.tracker_placeholder'),
                'btn_text' => __('home.tracker_btn'),
                'image' => '/assets/img/site/intro-scan.webp',
                'image_alt' => __('home.tracker_alt'),
                'width' => 900,
                'height' => 672,
            ],
            'intro' => [
                'title' => __('home.intro_title'),
                'paragraphs' => [
                    __('home.intro_p1'),
                    __('home.intro_p2'),
                ],
                'image' => '/assets/img/site/intro-scan.webp',
                'image_alt' => __('home.tracker_alt'),
                'width' => 900,
                'height' => 672,
            ],
            'features' => [
                [
                    'title' => __('home.feature_1_title'),
                    'description' => __('home.feature_1_desc'),
                    'link_text' => __('home.feature_1_link'),
                    'link_url' => '/contact',
                    'icon' => 'search',
                ],
                [
                    'title' => __('home.feature_2_title'),
                    'description' => __('home.feature_2_desc'),
                    'link_text' => __('home.feature_2_link'),
                    'link_url' => '/contact',
                    'icon' => 'check_circle',
                ],
                [
                    'title' => __('home.feature_3_title'),
                    'description' => __('home.feature_3_desc'),
                    'link_text' => __('home.feature_3_link'),
                    'link_url' => '/contact',
                    'icon' => 'schedule',
                ],
            ],

            // Section 1 : Solutions Gallery (4 cartes services + Mosaïque de 5 tuiles)
            'solutions_gallery' => [
                'title' => __('home.gallery_title'),
                'subtitle' => __('home.gallery_subtitle'),
                'cards' => [
                    [
                        'id' => 'expedition',
                        'title' => __('services.expedition_title'),
                        'description' => __('services.expedition_card_desc'),
                        'url' => '/services/expedition',
                        'icon' => 'inventory_2',
                    ],
                    [
                        'id' => 'suivi',
                        'title' => __('services.suivi_title'),
                        'description' => __('services.suivi_card_desc'),
                        'url' => '/services/suivi',
                        'icon' => 'track_changes',
                    ],
                    [
                        'id' => 'livraison',
                        'title' => __('services.livraison_title'),
                        'description' => __('services.livraison_card_desc'),
                        'url' => '/services/livraison',
                        'icon' => 'local_shipping',
                    ],
                    [
                        'id' => 'gestion',
                        'title' => __('services.gestion_title'),
                        'description' => __('services.gestion_card_desc'),
                        'url' => '/services/gestion',
                        'icon' => 'settings_suggest',
                    ],
                ],
                'mosaic' => [
                    [
                        'title' => __('home.gallery_mosaic_1'),
                        'service_name' => __('services.expedition_title'),
                        'url' => '/services/expedition',
                        'image' => '/assets/img/site/service-expedition.webp',
                        'image_alt' => __('home.gallery_mosaic_1'),
                        'width' => 900,
                        'height' => 672,
                        'grid_class' => 'col-span-12 sm:col-span-6 lg:col-span-4 h-72',
                    ],
                    [
                        'title' => __('home.gallery_mosaic_2'),
                        'service_name' => __('services.suivi_title'),
                        'url' => '/services/suivi',
                        'image' => '/assets/img/site/service-suivi.webp',
                        'image_alt' => __('home.gallery_mosaic_2'),
                        'width' => 896,
                        'height' => 1200,
                        'grid_class' => 'col-span-12 sm:col-span-6 lg:col-span-4 h-72',
                    ],
                    [
                        'title' => __('home.gallery_mosaic_3'),
                        'service_name' => __('services.livraison_title'),
                        'url' => '/services/livraison',
                        'image' => '/assets/img/site/service-livraison.webp',
                        'image_alt' => __('home.gallery_mosaic_3'),
                        'width' => 900,
                        'height' => 900,
                        'grid_class' => 'col-span-12 sm:col-span-6 lg:col-span-4 h-72',
                    ],
                    [
                        'title' => __('home.gallery_mosaic_4'),
                        'service_name' => __('services.gestion_title'),
                        'url' => '/services/gestion',
                        'image' => '/assets/img/site/service-gestion.webp',
                        'image_alt' => __('home.gallery_mosaic_4'),
                        'width' => 900,
                        'height' => 672,
                        'grid_class' => 'col-span-12 lg:col-span-7 h-80',
                    ],
                    [
                        'title' => __('home.gallery_mosaic_5'),
                        'service_name' => __('services.expedition_title') . ' & ' . __('services.suivi_title'),
                        'url' => '/services',
                        'image' => '/assets/img/site/section-qualite.webp',
                        'image_alt' => __('home.gallery_mosaic_5'),
                        'width' => 1376,
                        'height' => 768,
                        'grid_class' => 'col-span-12 lg:col-span-5 h-80',
                    ],
                ],
            ],

            // Section 2 : Section éditoriale
            'editorial' => [
                'title' => __('home.editorial_title'),
                'paragraphs' => [
                    __('home.editorial_p1'),
                    __('home.editorial_p2'),
                ],
            ],

            'services_detail' => [
                [
                    'id' => 'expedition',
                    'title' => __('services.expedition_title'),
                    'tag' => __('services.phase_1'),
                    'description' => __('service_expedition.desc'),
                    'items' => [
                        __('service_expedition.point_1'),
                        __('service_expedition.point_2'),
                        __('service_expedition.point_3'),
                        __('service_expedition.point_4'),
                    ],
                    'url' => '/services/expedition',
                    'image' => '/assets/img/site/service-expedition.webp',
                    'image_alt' => __('services.expedition_title'),
                    'width' => 900,
                    'height' => 672,
                    'reverse' => false,
                ],
                [
                    'id' => 'suivi',
                    'title' => __('services.suivi_title'),
                    'tag' => __('services.phase_2'),
                    'description' => __('service_suivi.desc'),
                    'items' => [
                        __('service_suivi.point_1'),
                        __('service_suivi.point_2'),
                        __('service_suivi.point_3'),
                        __('service_suivi.point_4'),
                    ],
                    'url' => '/services/suivi',
                    'image' => '/assets/img/site/service-suivi.webp',
                    'image_alt' => __('services.suivi_title'),
                    'width' => 896,
                    'height' => 1200,
                    'reverse' => true,
                ],
                [
                    'id' => 'livraison',
                    'title' => __('services.livraison_title'),
                    'tag' => __('services.phase_3'),
                    'description' => __('service_livraison.desc'),
                    'items' => [
                        __('service_livraison.point_1'),
                        __('service_livraison.point_2'),
                        __('service_livraison.point_3'),
                        __('service_livraison.point_4'),
                    ],
                    'url' => '/services/livraison',
                    'image' => '/assets/img/site/service-livraison.webp',
                    'image_alt' => __('services.livraison_title'),
                    'width' => 900,
                    'height' => 900,
                    'reverse' => false,
                ],
                [
                    'id' => 'gestion',
                    'title' => __('services.gestion_title'),
                    'tag' => __('services.supervision'),
                    'description' => __('service_gestion.desc'),
                    'items' => [
                        __('service_gestion.point_1'),
                        __('service_gestion.point_2'),
                        __('service_gestion.point_3'),
                        __('service_gestion.point_4'),
                    ],
                    'url' => '/services/gestion',
                    'image' => '/assets/img/site/service-gestion.webp',
                    'image_alt' => __('services.gestion_title'),
                    'width' => 900,
                    'height' => 672,
                    'reverse' => true,
                ],
            ],

            // Section 3 : Quality Tabs
            'quality_tabs' => [
                'title' => __('home.tabs_title'),
                'subtitle' => __('home.tabs_subtitle'),
                'image_bg' => '/assets/img/site/contact-quai.webp',
                'image_bg_alt' => __('home.tabs_title'),
                'width' => 1376,
                'height' => 768,
                'tabs' => [
                    [
                        'id' => 'tab-expedition',
                        'title' => __('home.tab_1_title'),
                        'badge' => __('home.tab_1_badge'),
                        'heading' => __('home.tab_1_heading'),
                        'description' => __('home.tab_1_desc'),
                        'points' => [
                            __('home.tab_1_point_1'),
                            __('home.tab_1_point_2'),
                            __('home.tab_1_point_3'),
                        ],
                        'url' => '/services/expedition',
                        'link_label' => __('home.tab_1_link'),
                    ],
                    [
                        'id' => 'tab-suivi',
                        'title' => __('home.tab_2_title'),
                        'badge' => __('home.tab_2_badge'),
                        'heading' => __('home.tab_2_heading'),
                        'description' => __('home.tab_2_desc'),
                        'points' => [
                            __('home.tab_2_point_1'),
                            __('home.tab_2_point_2'),
                            __('home.tab_2_point_3'),
                        ],
                        'url' => '/services/suivi',
                        'link_label' => __('home.tab_2_link'),
                    ],
                    [
                        'id' => 'tab-livraison',
                        'title' => __('home.tab_3_title'),
                        'badge' => __('home.tab_3_badge'),
                        'heading' => __('home.tab_3_heading'),
                        'description' => __('home.tab_3_desc'),
                        'points' => [
                            __('home.tab_3_point_1'),
                            __('home.tab_3_point_2'),
                            __('home.tab_3_point_3'),
                        ],
                        'url' => '/services/livraison',
                        'link_label' => __('home.tab_3_link'),
                    ],
                    [
                        'id' => 'tab-gestion',
                        'title' => __('home.tab_4_title'),
                        'badge' => __('home.tab_4_badge'),
                        'heading' => __('home.tab_4_heading'),
                        'description' => __('home.tab_4_desc'),
                        'points' => [
                            __('home.tab_4_point_1'),
                            __('home.tab_4_point_2'),
                            __('home.tab_4_point_3'),
                        ],
                        'url' => '/services/gestion',
                        'link_label' => __('home.tab_4_link'),
                    ],
                ],
            ],

            // Section 4 : Contact CTA
            'contact_cta' => [
                'title' => __('home.contact_cta_title'),
                'description' => __('home.contact_cta_desc'),
                'btn_text' => __('home.contact_cta_btn'),
                'btn_url' => '/contact',
            ],

            'services' => [
                [
                    'id' => 'expedition',
                    'slug' => 'expedition',
                    'title' => __('services.expedition_title'),
                    'description' => __('services.expedition_card_desc'),
                    'url' => '/services/expedition',
                ],
                [
                    'id' => 'suivi',
                    'slug' => 'suivi',
                    'title' => __('services.suivi_title'),
                    'description' => __('services.suivi_card_desc'),
                    'url' => '/services/suivi',
                ],
                [
                    'id' => 'livraison',
                    'slug' => 'livraison',
                    'title' => __('services.livraison_title'),
                    'description' => __('services.livraison_card_desc'),
                    'url' => '/services/livraison',
                ],
                [
                    'id' => 'gestion',
                    'slug' => 'gestion',
                    'title' => __('services.gestion_title'),
                    'description' => __('services.gestion_card_desc'),
                    'url' => '/services/gestion',
                ],
            ],
            'footer' => [
                'quick_links' => [
                    ['label' => __('nav.home'), 'url' => '/'],
                    ['label' => __('nav.services'), 'url' => '/services'],
                    ['label' => __('nav.track'), 'url' => '/track'],
                    ['label' => __('nav.contact'), 'url' => '/contact'],
                ],
                'legal_notice' => '[À compléter]',
                'privacy_notice' => '[À compléter]',
            ],
        ];
    }
}
