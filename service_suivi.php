<?php
// service_suivi.php
// Présentation du service Suivi — Traçabilité continue, statuts et événements horodatés
require_once __DIR__ . '/api/bootstrap.php';

$page_title = 'Service Suivi — Traçabilité continue et historique des colis';
$page_description = "Le suivi documente le parcours géographique du colis à chaque passage en centre de transit au fil des étapes du circuit.";
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => 'Traçabilité en continu',
    'title' => 'Suivi des colis et chronologie d\'acheminement',
    'description' => 'Le suivi documente le parcours géographique du colis à chaque passage en centre de transit. Les événements horodatés informent de l\'état courant au fil des étapes du circuit logistique.',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => '/'],
        ['label' => 'Services', 'url' => '/services'],
        ['label' => 'Suivi']
    ],
    'actions' => [
        ['label' => 'Accéder au formulaire de suivi', 'url' => '/track', 'primary' => true],
        ['label' => 'Une question ? Contactez-nous', 'url' => '/contact', 'primary' => false],
    ]
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <!-- Section Principes & Protection des données -->
    <section class="py-16 sm:py-20 bg-white" aria-labelledby="principles-heading">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                <div class="lg:col-span-6 space-y-6">
                    <h2 id="principles-heading" class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Fonctionnement de l'interface de suivi
                    </h2>
                    <p class="text-base text-[#333333] leading-relaxed">
                        Chaque événement enregistré dans la base de données associe un statut technique, un lieu de passage et un horodatage précis. Cette architecture permet de consulter à tout moment la progression sans ambiguïté.
                    </p>

                    <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                        <h3 class="text-base font-bold text-[#171717] mb-2">Protection et confidentialité</h3>
                        <p class="text-sm text-[#6B6B6B] leading-relaxed">
                            L'interface de suivi public anonymise les données sensibles : les noms et adresses complètes sont masqués pour préserver la vie privée des destinataires tout en conservant la visibilité des jalons de livraison.
                        </p>
                    </div>

                    <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                        <h3 class="text-base font-bold text-[#171717] mb-2">Représentation cartographique</h3>
                        <p class="text-sm text-[#6B6B6B] leading-relaxed">
                            Les coordonnées géographiques des points d'étape renseignés par les transporteurs alimentent un tracé d'itinéraire visuel, reliant l'origine, les étapes franchies et la destination prévue.
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-6 space-y-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Les 4 statuts officiels d'acheminement
                    </h2>
                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                        Chaque envoi suit un cycle d'états normalisés pour une lisibilité directe de l'avancement :
                    </p>

                    <div class="space-y-4">
                        <div class="bg-white rounded-xl p-5 border border-stone-200 shadow-sm flex items-start gap-4">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md shrink-0 border border-blue-200">
                                Expédié
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-[#171717]">Prise en charge initiale</h3>
                                <p class="text-xs sm:text-sm text-[#6B6B6B] mt-1">Le colis a été remis au transporteur et son enregistrement initial est validé.</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 border border-stone-200 shadow-sm flex items-start gap-4">
                            <span class="inline-block bg-amber-100 text-amber-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md shrink-0 border border-amber-200">
                                En cours
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-[#171717]">Transit et acheminement</h3>
                                <p class="text-xs sm:text-sm text-[#6B6B6B] mt-1">Le colis circule entre les centres de tri ou est engagé dans la tournée locale.</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 border border-stone-200 shadow-sm flex items-start gap-4">
                            <span class="inline-block bg-green-100 text-green-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md shrink-0 border border-green-200">
                                Livré
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-[#171717]">Remise effectuée</h3>
                                <p class="text-xs sm:text-sm text-[#6B6B6B] mt-1">Le colis a été remis au destinataire et le cycle d'expédition est clôturé.</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 border border-stone-200 shadow-sm flex items-start gap-4">
                            <span class="inline-block bg-red-100 text-red-800 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md shrink-0 border border-red-200">
                                Retardé
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-[#171717]">Incident de parcours</h3>
                                <p class="text-xs sm:text-sm text-[#6B6B6B] mt-1">Un aléa technique ou d'acheminement a été consigné par le transporteur.</p>
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
                Consulter un dossier d'expédition
            </h2>
            <p class="text-base text-[#171717] leading-relaxed mb-8">
                Accédez à la page de suivi public pour visualiser la chronologie détaillée et la localisation des points franchis.
            </p>
            <a href="/track" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-8 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                <span>Accéder à l'interface de suivi</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
