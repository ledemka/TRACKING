<?php
// templates/home/solutions_gallery.php
// Titre "Nos services", cartes à icônes des 4 services + mosaïque CSS Grid de 5 photos
$content = get_site_content();
$gallery = $content['solutions_gallery'];
?>
<section class="py-16 sm:py-24 bg-white" aria-labelledby="services-heading">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête de section -->
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] block mb-2">
                Offre logistique
            </span>
            <h2 id="services-heading" class="text-3xl sm:text-4xl font-extrabold text-[#171717] tracking-tight mb-4">
                <?= escape_html($gallery['title']) ?>
            </h2>
            <p class="text-base text-[#6B6B6B] leading-relaxed">
                <?= escape_html($gallery['subtitle']) ?>
            </p>
        </div>

        <!-- 4 Petites cartes à icônes pour les 4 services -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            <?php foreach ($gallery['cards'] as $card): ?>
                <a href="<?= escape_html($card['url']) ?>" class="group bg-[#F4F3ED] rounded-xl p-6 border border-stone-200 transition-all hover:border-[#171717] hover:shadow-sm flex flex-col justify-between focus-brand-light">
                    <div>
                        <div class="w-12 h-12 rounded-lg bg-[#171717] text-[#FFE500] flex items-center justify-center mb-5 group-hover:bg-[#FFE500] group-hover:text-[#171717] transition-colors">
                            <?php if ($card['icon'] === 'inventory_2'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            <?php elseif ($card['icon'] === 'track_changes'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            <?php elseif ($card['icon'] === 'local_shipping'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l3 3v8a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2z"/>
                                </svg>
                            <?php elseif ($card['icon'] === 'settings_suggest'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-lg font-bold text-[#171717] mb-2 group-hover:text-black">
                            <?= escape_html($card['title']) ?>
                        </h3>
                        <p class="text-sm text-[#6B6B6B] leading-relaxed mb-4">
                            <?= escape_html($card['description']) ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-[#171717] group-hover:translate-x-1 transition-transform">
                        <span>Détails</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Mosaïque CSS Grid de 5 photos -->
        <div class="grid grid-cols-12 gap-4 sm:gap-6">
            <?php foreach ($gallery['mosaic'] as $tile): ?>
                <a href="<?= escape_html($tile['url']) ?>" class="<?= escape_html($tile['grid_class']) ?> mosaic-tile group relative rounded-2xl overflow-hidden shadow-sm border border-stone-200 block focus-brand-light">
                    <!-- Image de fond -->
                    <img 
                        src="<?= escape_html($tile['image']) ?>" 
                        alt="<?= escape_html($tile['image_alt']) ?>" 
                        class="mosaic-img w-full h-full object-cover object-center absolute inset-0" 
                        width="<?= (int)$tile['width'] ?>" 
                        height="<?= (int)$tile['height'] ?>" 
                        loading="lazy"
                    >
                    <!-- Dégradé sombre pour lisibilité -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent"></div>

                    <!-- Contenu textuel sur la tuile -->
                    <div class="absolute inset-0 p-6 sm:p-8 flex flex-col justify-end">
                        <span class="inline-block bg-[#FFE500] text-[#171717] text-xs font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md mb-2 self-start">
                            <?= escape_html($tile['service_name']) ?>
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight drop-shadow-sm group-hover:text-[#FFE500] transition-colors">
                            <?= escape_html($tile['title']) ?>
                        </h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
