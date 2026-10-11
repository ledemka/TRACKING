<?php
// templates/home/company_intro.php
// Présentation en deux colonnes (texte + photo)
$content = get_site_content();
$intro = $content['intro'];
?>
<section class="py-12 sm:py-16 bg-[#F4F3ED] border-b border-stone-200">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <!-- Texte à gauche -->
            <div class="lg:col-span-7 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] block">
                    Organisation logistique
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-[#171717] tracking-tight">
                    <?= escape_html($intro['title']) ?>
                </h2>
                <div class="space-y-4 pt-2">
                    <?php foreach ($intro['paragraphs'] as $p): ?>
                        <p class="text-base text-[#333333] leading-relaxed">
                            <?= escape_html($p) ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Photo à droite -->
            <div class="lg:col-span-5">
                <div class="rounded-xl overflow-hidden shadow-sm border border-stone-200">
                    <img 
                        src="<?= escape_html($intro['image']) ?>" 
                        alt="<?= escape_html($intro['image_alt']) ?>" 
                        class="w-full h-auto object-cover" 
                        width="<?= (int)$intro['width'] ?>" 
                        height="<?= (int)$intro['height'] ?>" 
                        loading="lazy"
                    >
                </div>
            </div>
        </div>
    </div>
</section>
