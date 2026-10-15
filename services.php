<?php
// services.php
// Page générale de présentation des 4 services logistiques de TRACKING
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/templates/site_content.php';

$content = get_site_content();
$nomEntreprise = $content['brand']['nom'];
$services = $content['services'];
$editorial = $content['editorial'];
$details = $content['services_detail'];

$page_title = __('services.page_title');
$page_description = __('services.page_desc');
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => __('services.banner_badge'),
    'title' => __('services.banner_title'),
    'description' => __('services.banner_desc'),
    'breadcrumbs' => [
        ['label' => __('nav.home'), 'url' => '/'],
        ['label' => __('nav.services')]
    ],
    'actions' => [
        ['label' => __('services.banner_track_action'), 'url' => '/track', 'primary' => true],
        ['label' => __('services.banner_contact_action'), 'url' => '/contact', 'primary' => false],
    ],
    'image' => '/assets/img/site/section-qualite.webp',
    'image_alt' => __('services.banner_title')
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <!-- Grille récapitulative des 4 services avec accès direct -->
    <section class="py-16 sm:py-20 bg-[#F4F3ED] border-b border-stone-200" aria-label="<?= __attr('nav.services') ?>">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] block mb-2"><?= escape_html(__('services.structure_badge')) ?></span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight mb-4">
                    <?= escape_html(__('services.structure_title')) ?>
                </h2>
                <p class="text-base text-[#6B6B6B] leading-relaxed">
                    <?= escape_html(__('services.structure_desc')) ?>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php 
                $serviceCards = [
                    [
                        'tag' => __('services.phase_1'),
                        'title' => __('services.expedition_title'),
                        'desc' => __('services.expedition_card_desc'),
                        'url' => '/services/expedition',
                        'btn' => __('services.expedition_btn')
                    ],
                    [
                        'tag' => __('services.phase_2'),
                        'title' => __('services.suivi_title'),
                        'desc' => __('services.suivi_card_desc'),
                        'url' => '/services/suivi',
                        'btn' => __('services.suivi_btn')
                    ],
                    [
                        'tag' => __('services.phase_3'),
                        'title' => __('services.livraison_title'),
                        'desc' => __('services.livraison_card_desc'),
                        'url' => '/services/livraison',
                        'btn' => __('services.livraison_btn')
                    ],
                    [
                        'tag' => __('services.supervision'),
                        'title' => __('services.gestion_title'),
                        'desc' => __('services.gestion_card_desc'),
                        'url' => '/services/gestion',
                        'btn' => __('services.gestion_btn')
                    ],
                ];
                foreach ($serviceCards as $card):
                ?>
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-stone-200 shadow-sm flex flex-col justify-between service-card-hover">
                    <div>
                        <span class="inline-block bg-[#171717] text-[#FFE500] text-xs font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md mb-4">
                            <?= escape_html($card['tag']) ?>
                        </span>
                        <h3 class="text-xl font-bold text-[#171717] tracking-tight mb-3">
                            <?= escape_html($card['title']) ?>
                        </h3>
                        <p class="text-sm text-[#333333] leading-relaxed mb-6">
                            <?= escape_html($card['desc']) ?>
                        </p>
                    </div>
                    <div>
                        <a href="<?= escape_html($card['url']) ?>" class="inline-flex items-center gap-2 text-sm font-bold text-[#171717] hover:text-[#B5A000] transition-colors focus-brand-light rounded py-1">
                            <span><?= escape_html($card['btn']) ?></span>
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Détail des 4 maillons avec photos et prestations -->
    <section class="py-16 sm:py-24 bg-white" aria-label="Détail des prestations">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16 sm:mb-20">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] block mb-2">Méthode & Rigueur</span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#171717] tracking-tight mb-4">
                    <?= escape_html($editorial['title']) ?>
                </h2>
                <div class="space-y-3 text-base text-[#333333] leading-relaxed text-left sm:text-center">
                    <?php foreach ($editorial['paragraphs'] as $p): ?>
                        <p><?= escape_html($p) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-16 sm:space-y-24">
                <?php foreach ($details as $idx => $serv): ?>
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-6 <?= $serv['reverse'] ? 'lg:order-2' : 'lg:order-1' ?>">
                            <div class="rounded-2xl overflow-hidden shadow-sm border border-stone-200 bg-white">
                                <img 
                                    src="<?= escape_html($serv['image']) ?>" 
                                    alt="<?= escape_html($serv['image_alt']) ?>" 
                                    class="w-full h-80 sm:h-96 object-cover object-center" 
                                    width="<?= (int)$serv['width'] ?>" 
                                    height="<?= (int)$serv['height'] ?>" 
                                    loading="lazy"
                                >
                            </div>
                        </div>

                        <div class="lg:col-span-6 <?= $serv['reverse'] ? 'lg:order-1' : 'lg:order-2' ?> space-y-5">
                            <span class="inline-block bg-[#171717] text-[#FFE500] text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md">
                                <?= escape_html($serv['tag']) ?>
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                                <?= escape_html($serv['title']) ?>
                            </h3>
                            <p class="text-base text-[#333333] leading-relaxed">
                                <?= escape_html($serv['description']) ?>
                            </p>
                            
                            <ul class="space-y-2.5 pt-2" aria-label="Prestations de <?= escape_html($serv['title']) ?>">
                                <?php foreach ($serv['items'] as $item): ?>
                                    <li class="flex items-start gap-3 text-sm sm:text-base text-[#333333]">
                                        <svg class="w-5 h-5 text-[#B5A000] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span><?= escape_html($item) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="pt-4">
                                <a href="<?= escape_html($serv['url']) ?>" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-5 py-3 rounded-lg shadow-sm transition-colors focus-brand-dark">
                                    <span>Consulter la page dédiée <?= escape_html($serv['title']) ?></span>
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Appel à l'action -->
    <?php require __DIR__ . '/templates/home/contact_cta.php'; ?>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
