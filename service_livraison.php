<?php
// service_livraison.php
// Présentation du service Livraison — Distribution locale, remise au destinataire et clôture
require_once __DIR__ . '/api/bootstrap.php';

$page_title = 'Service Livraison — Acheminement final et remise des colis';
$page_description = "La livraison constitue le dernier maillon de l'acheminement, de la prise en charge locale jusqu'à la remise et clôture de l'envoi.";
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => 'Étape finale de distribution',
    'title' => 'Livraison et remise au destinataire',
    'description' => 'La livraison constitue le dernier maillon de l\'acheminement logistique. Elle regroupe la prise en charge par les équipes de distribution locale jusqu\'à la remise physique et la clôture de l\'envoi.',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => '/'],
        ['label' => 'Services', 'url' => '/services'],
        ['label' => 'Livraison']
    ],
    'actions' => [
        ['label' => 'Vérifier un colis en livraison', 'url' => '/track', 'primary' => true],
        ['label' => 'Une question sur la remise ?', 'url' => '/contact', 'primary' => false],
    ]
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <!-- Section Déroulement de la livraison -->
    <section class="py-16 sm:py-20 bg-white" aria-labelledby="delivery-process-heading">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                <div class="lg:col-span-6 space-y-6">
                    <h2 id="delivery-process-heading" class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Organisation de la remise physique
                    </h2>
                    <p class="text-base text-[#333333] leading-relaxed">
                        Lorsque le colis parvient à l'agence de distribution de proximité, son statut bascule à « En cours de livraison ». Cette phase active la préparation de la tournée locale de remise.
                    </p>
                    <p class="text-base text-[#333333] leading-relaxed">
                        La remise physique s'effectue à l'adresse indiquée lors de l'enregistrement initial. À l'achèvement de cette opération, la confirmation de remise clôture la chronologie de suivi.
                    </p>

                    <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#171717] mb-3">
                            Signification du statut final « Livré »
                        </h3>
                        <p class="text-sm text-[#6B6B6B] leading-relaxed mb-4">
                            L'enregistrement du statut « Livré » confirme que le colis a été réceptionné par le destinataire ou déposé au point convenu. L'événement est archivé dans l'historique de traçabilité.
                        </p>
                        <div class="flex items-center gap-3 bg-white p-3.5 rounded-lg border border-stone-200">
                            <span class="inline-block bg-green-100 text-green-800 text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md border border-green-200">
                                Statut final
                            </span>
                            <span class="text-sm font-semibold text-[#171717]">Cycle d'acheminement mené à son terme</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 space-y-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                        Les jalons de la tournée finale
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">1</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Arrivée au centre local</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Réception du colis au dépôt de proximité et scan d'entrée pour validation de l'itinéraire régional.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">2</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Prise en charge par le livreur</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Chargement dans le véhicule de distribution et passage au statut « En cours de livraison ».
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 service-card-hover">
                            <div class="flex items-start gap-4">
                                <span class="w-8 h-8 rounded-lg bg-[#171717] text-[#FFE500] font-bold text-sm flex items-center justify-center shrink-0">3</span>
                                <div>
                                    <h3 class="text-base font-bold text-[#171717] mb-1">Remise et clôture du dossier</h3>
                                    <p class="text-sm text-[#6B6B6B] leading-relaxed">
                                        Validation de la remise physique, horodatage de l'événement et mise à jour de l'historique public.
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
                Suivre l'acheminement jusqu'au destinataire
            </h2>
            <p class="text-base text-[#171717] leading-relaxed mb-8">
                Consultez à tout moment la progression de vos envois sur notre outil de traçabilité en ligne.
            </p>
            <a href="/track" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-8 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                <span>Accéder au suivi</span>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
