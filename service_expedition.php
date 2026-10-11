<?php
// service_expedition.php
// Présentation du service Expédition — Enregistrement, conditionnement et prise en charge initiale
require_once __DIR__ . '/api/bootstrap.php';

$page_title = 'Service Expédition — Prise en charge et enregistrement des colis';
$page_description = "L'expédition formalise la prise en charge et l'étiquetage de chaque colis au sein du réseau de distribution.";
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => 'Étape initiale du circuit',
    'title' => 'Expédition et préparation des envois',
    'description' => 'L\'expédition formalise la prise en charge de chaque colis au sein du réseau de distribution. Chaque envoi reçoit un identifiant unique assurant la continuité de sa traçabilité dès son enregistrement.',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => '/'],
        ['label' => 'Services', 'url' => '/services'],
        ['label' => 'Expédition']
    ],
    'actions' => [
        ['label' => 'Accéder au suivi d\'envoi', 'url' => '/track', 'primary' => true],
        ['label' => 'Poser une question', 'url' => '/contact', 'primary' => false],
    ]
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
                        Le rôle de l'étape d'expédition
                    </h2>
                    <p class="text-base text-[#333333] leading-relaxed">
                        L'étape d'expédition constitue le point de départ de tout acheminement. Elle permet d'établir les enregistrements techniques nécessaires avant l'intégration du colis dans les flux logistiques.
                    </p>
                    <p class="text-base text-[#333333] leading-relaxed">
                        Chaque élément transmis est vérifié afin d'associer un numéro de référence consultable à l'adresse de destination, au transporteur assigné et aux caractéristiques du colis.
                    </p>

                    <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#171717] mb-3">
                            Données enregistrées à l'expédition
                        </h3>
                        <ul class="space-y-2.5 text-sm sm:text-base text-[#333333]" aria-label="Données enregistrées">
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Numéro de suivi unique au format normalisé</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Coordonnées géographiques de départ et d'arrivée</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Date et horodatage de la prise en charge</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Transporteur opérationnel en charge du trajet</span>
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
