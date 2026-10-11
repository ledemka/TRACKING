<?php
// templates/home/services_section.php
// Section éditoriale centrée + 4 sections alternées image/texte par service avec prestations factuelles
$content = get_site_content();
$editorial = $content['editorial'];
$details = $content['services_detail'];
?>
<section class="py-16 sm:py-24 bg-[#F4F3ED] border-y border-stone-200">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section éditoriale centrée (largeur de lecture limitée) -->
        <div class="max-w-3xl mx-auto text-center mb-20 sm:mb-28">
            <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] block mb-3">
                Traçabilité & Méthode
            </span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#171717] tracking-tight mb-6">
                <?= escape_html($editorial['title']) ?>
            </h2>
            <div class="space-y-4 text-base text-[#333333] leading-relaxed text-left sm:text-center">
                <?php foreach ($editorial['paragraphs'] as $p): ?>
                    <p><?= escape_html($p) ?></p>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4 Sections services alternant image/texte -->
        <div class="space-y-16 sm:space-y-24">
            <?php foreach ($details as $idx => $serv): ?>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <!-- Colonne Image -->
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

                    <!-- Colonne Texte -->
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
                        
                        <!-- Liste de prestations factuelles -->
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
                                <span>Consulter la page <?= escape_html($serv['title']) ?></span>
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
