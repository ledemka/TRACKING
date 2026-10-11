<?php
// templates/home/contact_cta.php
// Bandeau jaune #FFE500 avec bouton noir #171717 vers /contact
$content = get_site_content();
$cta = $content['contact_cta'];
?>
<section class="bg-[#FFE500] text-[#171717] py-14 sm:py-20 border-t border-stone-200">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#171717] mb-4">
                <?= escape_html($cta['title']) ?>
            </h2>
            <p class="text-base sm:text-lg text-[#171717] leading-relaxed mb-8 max-w-2xl mx-auto">
                <?= escape_html($cta['description']) ?>
            </p>
            <div>
                <a href="<?= escape_html($cta['btn_url']) ?>" class="inline-flex items-center gap-2.5 bg-[#171717] text-white hover:bg-black font-bold text-base px-8 py-4 rounded-xl shadow-md transition-all focus-brand-light">
                    <span><?= escape_html($cta['btn_text']) ?></span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
