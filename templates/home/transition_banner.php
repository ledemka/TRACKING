<?php
// templates/home/transition_banner.php
// Bandeau de transition : phrase courte, centrée
$content = get_site_content();
$bannerText = $content['transition_banner']['text'];
?>
<section class="bg-[#F4F3ED] border-y border-stone-200 py-6 px-4">
    <div class="max-w-[1280px] mx-auto text-center">
        <p class="text-base sm:text-lg font-medium text-[#333333] tracking-wide">
            <?= escape_html($bannerText) ?>
        </p>
    </div>
</section>
