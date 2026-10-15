<?php
// service_gestion.php
// Présentation du service Gestion des expéditions — Supervision coordonnée des volumes et suivi centralisé
require_once __DIR__ . '/api/bootstrap.php';

$page_title = __('service_gestion.page_title');
$page_description = __('service_gestion.page_desc');
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => __('service_gestion.badge'),
    'title' => __('service_gestion.title'),
    'description' => __('service_gestion.desc'),
    'breadcrumbs' => [
        ['label' => __('nav.home'), 'url' => '/'],
        ['label' => __('nav.services'), 'url' => '/services'],
        ['label' => __('services.gestion_title')]
    ],
    'actions' => [
        ['label' => __('service_gestion.btn_track'), 'url' => '/track', 'primary' => true],
        ['label' => __('service_gestion.btn_contact'), 'url' => '/contact', 'primary' => false],
    ],
    'image' => '/assets/img/site/service-gestion.webp',
    'image_alt' => __('service_gestion.title')
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <!-- Section Organisation et Contrôle -->
    <section class="py-16 sm:py-20 bg-white" aria-labelledby="management-heading">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                <div class="lg:col-span-6 space-y-6">
                    <h2 id="management-heading" class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Une structure ordonnée des flux logistiques
                    </h2>
                    <p class="text-base text-[#333333] leading-relaxed">
                        L'organisation des expéditions repose sur un référentiel unique où chaque colis est répertorié avec ses paramètres d'origine, sa destination finale et ses événements intermédiaires.
                    </p>
                    <p class="text-base text-[#333333] leading-relaxed">
                        Cette centralisation assure la cohérence des données transmises à chaque point de contrôle et permet une restitution cartographique précise des déplacements.
                    </p>

                    <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#171717] mb-3">
                            Fonctions clés de gestion
                        </h3>
                        <ul class="space-y-2.5 text-sm sm:text-base text-[#333333]" aria-label="Fonctions clés">
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Attribution et archivage des numéros de référence d'envoi</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Coordination des mises à jour de statuts d'acheminement</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Consignation horodatée des passages en centre logistique</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Signalement méthodique des retards et anomalies de transit</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-6 space-y-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Supervision par étapes d'avancement
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">1</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Enregistrement et vérification initiale</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Contrôle des données de l'envoi, rattachement au transporteur et création de la fiche d'expédition.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">2</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Traçabilité des points d'étape</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Intégration continue des événements de transit transmis par les terminaux optiques de manutention.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">3</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Clôture et historique</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Confirmation finale de remise par le transporteur et archivage des données pour consultation ultérieure.
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
                Une question sur la gestion de vos flux ?
            </h2>
            <p class="text-base text-[#171717] leading-relaxed mb-8">
                Pour toute demande d'information sur les procédures d'expédition et les outils de suivi, contactez nos équipes.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/track" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-8 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                    <span>Suivre un colis</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
                <a href="/contact" class="inline-flex items-center gap-2 bg-transparent text-[#171717] border-2 border-[#171717] hover:bg-[#171717] hover:text-white font-bold text-sm px-8 py-3.5 rounded-lg transition-colors focus-brand-light">
                    <span>Nous contacter</span>
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
