<?php
// service_expedition.php
// Présentation du service Expédition — Enregistrement, conditionnement et prise en charge initiale
require_once __DIR__ . '/api/bootstrap.php';

$page_title = __('service_expedition.page_title');
$page_description = __('service_expedition.page_desc');
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => __('service_expedition.badge'),
    'title' => __('service_expedition.title'),
    'description' => __('service_expedition.desc'),
    'breadcrumbs' => [
        ['label' => __('nav.home'), 'url' => '/'],
        ['label' => __('nav.services'), 'url' => '/services'],
        ['label' => __('services.expedition_title')]
    ],
    'actions' => [
        ['label' => __('service_expedition.btn_track'), 'url' => '/track', 'primary' => true],
        ['label' => __('service_expedition.btn_contact'), 'url' => '/contact', 'primary' => false],
    ],
    'image' => '/assets/img/site/service-expedition.webp',
    'image_alt' => __('service_expedition.title')
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <!-- Section Éditoriale & Rôle de l'expédition -->
    <section class="py-16 sm:py-20 bg-white" aria-labelledby="role-heading">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                <div class="lg:col-span-6 space-y-6">
                    <h2 id="role-heading" class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        <?= escape_html(__('service_expedition.role_title')) ?>
                    </h2>
                    <p class="text-base text-[#333333] leading-relaxed">
                        <?= escape_html(__('service_expedition.role_p1')) ?>
                    </p>
                    <p class="text-base text-[#333333] leading-relaxed">
                        <?= escape_html(__('service_expedition.role_p2')) ?>
                    </p>

                    <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#171717] mb-3">
                            <?= escape_html(__('service_expedition.box_title')) ?>
                        </h3>
                        <ul class="space-y-2.5 text-sm sm:text-base text-[#333333]" aria-label="<?= __attr('service_expedition.box_title') ?>">
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span><?= escape_html(__('service_expedition.point_1')) ?></span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span><?= escape_html(__('service_expedition.point_2')) ?></span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span><?= escape_html(__('service_expedition.point_3')) ?></span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span><?= escape_html(__('service_expedition.point_4')) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-6 space-y-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Parcours méthodique en 3 temps
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">1</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Conditionnement et étiquetage</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Fermeture conforme du colis et apposition de l'étiquette avec code-barres normalisé permettant les lectures optiques automatiques.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">2</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Injection dans le système de suivi</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Transmission instantanée de l'identifiant dans la base centrale de données, rendant l'envoi consultable sur la page de suivi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">3</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Transfert vers la plateforme de tri</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Attribution du statut « Expédié » et chargement du colis vers le premier centre de transit de l'itinéraire.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bandeau CTA harmonisé -->
    <section class="py-12 sm:py-16 bg-[#FFE500] text-[#171717]" aria-label="Action de suivi">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight mb-4">
                Vous disposez d'un numéro d'expédition ?
            </h2>
            <p class="text-base text-[#171717] leading-relaxed mb-8">
                Renseignez votre référence pour visualiser les points d'étape enregistrés et la progression de votre envoi.
            </p>
            <a href="/track" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-8 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                <span>Vérifier le statut d'un colis</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
