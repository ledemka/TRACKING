<?php
// templates/home/hero_carousel.php
// Hero carrousel 3 slides sans saut de mise en page, conforme accessibilité
$content = get_site_content();
$slides = $content['hero_slides'];
?>
<section class="relative bg-[#171717] overflow-hidden" role="region" aria-roledescription="carousel" aria-label="Présentation des services" data-hero-carousel>
    <!-- Conteneur fixe pour éviter tout CLS -->
    <div class="relative w-full h-[520px] sm:h-[580px] lg:h-[640px]">
        <?php foreach ($slides as $index => $slide): 
            $isFirst = ($index === 0);
            $slideNum = $index + 1;
        ?>
            <div class="hero-slide w-full h-full <?= $isFirst ? 'is-active' : '' ?>" role="group" aria-roledescription="slide" aria-label="<?= $slideNum ?> sur <?= count($slides) ?>" <?= !$isFirst ? 'aria-hidden="true"' : '' ?>>
                <!-- Image d'arrière-plan avec voile sombre -->
                <div class="absolute inset-0 z-0">
                    <img 
                        src="<?= escape_html($slide['image']) ?>" 
                        alt="<?= escape_html($slide['image_alt']) ?>" 
                        class="w-full h-full object-cover object-center"
                        width="<?= (int)$slide['width'] ?>" 
                        height="<?= (int)$slide['height'] ?>"
                        <?= $isFirst ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                    >
                    <!-- Voile sombre contrasté pour lisibilité du texte -->
                    <div class="absolute inset-0 bg-black/60 bg-gradient-to-r from-black/80 via-black/60 to-transparent"></div>
                </div>

                <!-- Contenu textuel de la slide -->
                <div class="relative z-10 max-w-[1280px] mx-auto h-full px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
                    <div class="max-w-2xl text-white">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight mb-4 drop-shadow-sm">
                            <?= escape_html($slide['title']) ?>
                        </h1>
                        <p class="text-base sm:text-lg text-slate-200 mb-8 leading-relaxed max-w-xl">
                            <?= escape_html($slide['subtitle']) ?>
                        </p>
                        <div>
                            <a href="<?= escape_html($slide['btn_url']) ?>" class="inline-flex items-center gap-2 bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-base px-6 py-3.5 rounded-lg shadow-md transition-all focus-brand-dark">
                                <span><?= escape_html($slide['btn_text']) ?></span>
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Contrôles du carrousel -->
    <div class="absolute bottom-6 left-0 right-0 z-20">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <!-- Indicateurs de diapositives -->
            <div class="flex items-center gap-2" role="tablist" aria-label="Sélectionner une diapositive">
                <?php foreach ($slides as $index => $slide): 
                    $isFirst = ($index === 0);
                    $slideNum = $index + 1;
                ?>
                    <button 
                        type="button" 
                        class="h-3 w-8 rounded-full bg-[#FFE500] transition-all focus-brand-dark <?= $isFirst ? 'opacity-100' : 'opacity-40' ?>" 
                        data-hero-indicator 
                        data-index="<?= $index ?>" 
                        aria-label="Diapositive <?= $slideNum ?> sur <?= count($slides) ?>" 
                        aria-current="<?= $isFirst ? 'true' : 'false' ?>"
                    ></button>
                <?php endforeach; ?>
            </div>

            <!-- Flèches Précédent / Suivant et Bouton Pause/Lecture -->
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    class="p-2.5 rounded-full bg-black/40 hover:bg-black/70 text-white transition-colors focus-brand-dark border border-white/20" 
                    data-hero-pause 
                    aria-label="Activer le défilement automatique du carrousel"
                >
                    <svg class="w-4 h-4" data-icon-play fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg class="w-4 h-4 hidden" data-icon-pause fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>

                <button 
                    type="button" 
                    class="p-2.5 rounded-full bg-black/40 hover:bg-black/70 text-white transition-colors focus-brand-dark border border-white/20" 
                    data-hero-prev 
                    aria-label="Diapositive précédente"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <button 
                    type="button" 
                    class="p-2.5 rounded-full bg-black/40 hover:bg-black/70 text-white transition-colors focus-brand-dark border border-white/20" 
                    data-hero-next 
                    aria-label="Diapositive suivante"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>
