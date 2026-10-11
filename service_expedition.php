<?php
// service_expedition.php
// Présentation du service Expédition — Enregistrement, conditionnement et prise en charge initiale
require_once __DIR__ . '/api/bootstrap.php';

$page_title = 'Service Expédition — Prise en charge et enregistrement des colis';
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col bg-white">
    <!-- Fil d'Ariane -->
    <nav class="bg-[#F4F3ED] border-b border-stone-200 py-3 px-4 sm:px-6 lg:px-8" aria-label="Fil d'Ariane">
        <div class="max-w-[1280px] mx-auto flex items-center gap-2 text-xs font-semibold text-[#6B6B6B]">
            <a href="/" class="hover:text-[#171717] transition-colors focus-brand-light">Accueil</a>
            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-stone-400">Services</span>
            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-[#171717]" aria-current="page">Expédition</span>
        </div>
    </nav>

    <!-- Hero de présentation -->
    <section class="py-12 sm:py-16 lg:py-20 bg-[#171717] text-white relative overflow-hidden" aria-labelledby="service-title">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                <div class="lg:col-span-7">
                    <span class="inline-block bg-[#FFE500] text-[#171717] text-xs font-extrabold uppercase tracking-wider px-3 py-1.5 rounded-md mb-4">
                        Étape initiale du circuit
                    </span>
                    <h1 id="service-title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-6">
                        Expédition et préparation des envois
                    </h1>
                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mb-8">
                        L'expédition formalise la prise en charge de chaque colis au sein du réseau de distribution. Chaque envoi reçoit un identifiant unique assurant la continuité de sa traçabilité dès son enregistrement.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="/track" class="inline-flex items-center gap-2 bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                            <span>Accéder au suivi d'envoi</span>
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="/contact" class="inline-flex items-center gap-2 bg-transparent text-white border border-white/20 hover:bg-white/10 font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-dark">
                            <span>Poser une question</span>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="relative rounded-2xl overflow-hidden border border-white/15 shadow-2xl bg-black">
                        <img 
                            src="/assets/img/site/service-expedition.webp" 
                            alt="Préparation et fermeture d'un carton d'expédition avec ruban adhésif" 
                            class="w-full h-auto object-cover object-center" 
                            width="900" 
                            height="672" 
                            fetchpriority="high"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>

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
