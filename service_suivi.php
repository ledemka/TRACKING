<?php
// service_suivi.php
// Présentation du service Suivi — Traçabilité continue, statuts et événements horodatés
require_once __DIR__ . '/api/bootstrap.php';

$page_title = 'Service Suivi — Traçabilité continue et historique des colis';
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
            <span class="text-[#171717]" aria-current="page">Suivi</span>
        </div>
    </nav>

    <!-- Hero de présentation -->
    <section class="py-12 sm:py-16 lg:py-20 bg-[#171717] text-white relative overflow-hidden" aria-labelledby="service-title">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                <div class="lg:col-span-7">
                    <span class="inline-block bg-[#FFE500] text-[#171717] text-xs font-extrabold uppercase tracking-wider px-3 py-1.5 rounded-md mb-4">
                        Traçabilité en continu
                    </span>
                    <h1 id="service-title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-6">
                        Suivi des colis et chronologie d'acheminement
                    </h1>
                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mb-8">
                        Le suivi documente le parcours géographique du colis à chaque passage en centre de transit. Les événements horodatés informent de l'état courant au fil des étapes du circuit logistique.
                    </p>

                    <!-- Formulaire de recherche directe intégré -->
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/15 max-w-xl">
                        <form action="/track" method="GET" class="flex flex-col sm:flex-row gap-3">
                            <label for="track-input-service" class="sr-only">Numéro de suivi</label>
                            <input 
                                type="text" 
                                id="track-input-service"
                                name="id" 
                                placeholder="Ex. COLIS-2026-00125" 
                                class="flex-grow bg-white text-[#171717] px-4 py-3 rounded-lg text-sm font-semibold placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[#FFE500]"
                                required
                            >
                            <button type="submit" class="bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-6 py-3 rounded-lg transition-colors focus-brand-light whitespace-nowrap shadow-sm">
                                Suivre
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="relative rounded-2xl overflow-hidden border border-white/15 shadow-2xl bg-black">
                        <img 
                            src="/assets/img/site/service-suivi.webp" 
                            alt="Lecture optique d'un code-barres sur un colis dans une zone logistique" 
                            class="w-full h-auto object-cover object-center" 
                            width="896" 
                            height="1200" 
                            fetchpriority="high"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>

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
