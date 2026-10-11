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
            <h2 id="services-heading" class="text-3xl sm:text-4xl font-extrabold text-[#171717] tracking-tight mb-4">
                <?= escape_html($gallery['title']) ?>
            </h2>
            <p class="text-base text-[#6B6B6B] leading-relaxed">
                <?= escape_html($gallery['subtitle']) ?>
            </p>
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
