<?php
// templates/home/feature_cards.php
// 3 cartes à icône : "Suivi simple", "Statuts clairs", "Informations à jour", chacune menant à /contact
$content = get_site_content();
$features = $content['features'];
?>
<section class="py-12 sm:py-16 bg-white">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($features as $card): ?>
                <div class="bg-[#F4F3ED] rounded-xl p-6 sm:p-8 border border-stone-200 flex flex-col justify-between transition-all hover:border-stone-400 hover:shadow-sm">
                    <div>
                        <!-- Icône SVG intégrée -->
                        <div class="w-12 h-12 rounded-lg bg-[#FFE500] text-[#171717] flex items-center justify-center mb-6">
                            <?php if ($card['icon'] === 'search'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            <?php elseif ($card['icon'] === 'check_circle'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            <?php elseif ($card['icon'] === 'schedule'): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-xl font-bold text-[#171717] mb-3">
                            <?= escape_html($card['title']) ?>
                        </h3>
                        <p class="text-sm sm:text-base text-[#333333] leading-relaxed mb-6">
                            <?= escape_html($card['description']) ?>
                        </p>
                    </div>

                    <div>
                        <a href="<?= escape_html($card['link_url']) ?>" class="inline-flex items-center gap-1.5 text-sm font-bold text-[#171717] hover:underline focus-brand-light">
                            <span><?= escape_html($card['link_text']) ?></span>
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
