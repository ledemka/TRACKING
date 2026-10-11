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

$page_title = 'Nos Services — Organisation et acheminement logistique';
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col bg-white">
    <!-- Fil d'Ariane -->
    <nav class="bg-[#F4F3ED] border-b border-stone-200 py-3 px-4 sm:px-6 lg:px-8" aria-label="Fil d'Ariane">
        <div class="max-w-[1280px] mx-auto flex items-center gap-2 text-xs font-semibold text-[#6B6B6B]">
            <a href="/" class="hover:text-[#171717] transition-colors focus-brand-light rounded">Accueil</a>
            <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-[#171717]" aria-current="page">Services</span>
        </div>
    </nav>

    <!-- En-tête / Hero général des services -->
    <section class="py-12 sm:py-16 lg:py-20 bg-[#171717] text-white relative overflow-hidden" aria-labelledby="services-hub-title">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl">
                <span class="inline-block bg-[#FFE500] text-[#171717] text-xs font-extrabold uppercase tracking-wider px-3 py-1.5 rounded-md mb-4">
                    Offre de services
                </span>
                <h1 id="services-hub-title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-6">
                    Nos services d'acheminement et de suivi logistique
                </h1>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed mb-8">
                    Découvrez les quatre maillons essentiels de notre chaîne logistique : de la prise en charge initiale de votre colis jusqu'à sa remise finale au destinataire.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="/track" class="inline-flex items-center gap-2 bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                        <span>Suivre une expédition</span>
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    <a href="/contact" class="inline-flex items-center gap-2 bg-transparent text-white border border-white/20 hover:bg-white/10 font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-dark">
                        <span>Contacter notre équipe</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Grille récapitulative des 4 services avec accès direct -->
    <section class="py-16 sm:py-20 bg-[#F4F3ED] border-b border-stone-200" aria-label="Liste des services">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] block mb-2">Structure du circuit</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight mb-4">
                    Quatre expertises dédiées à chaque jalon
                </h2>
                <p class="text-base text-[#6B6B6B] leading-relaxed">
                    Chaque phase dispose d'outils de contrôle et d'enregistrement assurant la continuité de l'information.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php 
                $serviceCards = [
                    [
                        'tag' => 'Phase 1',
                        'title' => 'Expédition',
                        'desc' => 'Prise en charge initiale, pesée, étiquetage normalisé et saisie des données d\'acheminement.',
                        'url' => '/services/expedition',
                        'btn' => 'Découvrir l\'expédition'
                    ],
                    [
                        'tag' => 'Phase 2',
                        'title' => 'Suivi',
                        'desc' => 'Traçabilité en temps réel, relevé des jalons de transit et visualisation de l\'itinéraire.',
                        'url' => '/services/suivi',
                        'btn' => 'Découvrir le suivi'
                    ],
                    [
                        'tag' => 'Phase 3',
                        'title' => 'Livraison',
                        'desc' => 'Tournée locale finale, remise au destinataire et enregistrement de clôture de l\'envoi.',
                        'url' => '/services/livraison',
                        'btn' => 'Découvrir la livraison'
                    ],
                    [
                        'tag' => 'Supervision',
                        'title' => 'Gestion des expéditions',
                        'desc' => 'Supervision coordonnée des volumes, contrôle des statuts et consolidation des données.',
                        'url' => '/services/gestion',
                        'btn' => 'Découvrir la gestion'
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
