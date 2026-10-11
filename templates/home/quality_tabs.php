<?php
// templates/home/quality_tabs.php
// Section "Un suivi clair à chaque étape" avec onglets accessibles (tablist/tab/tabpanel) et carte blanche flottante
$content = get_site_content();
$quality = $content['quality_tabs'];
?>
<section class="py-16 sm:py-24 bg-[#171717] text-white relative overflow-hidden" aria-labelledby="quality-heading">
    <!-- Image de fond en filigrane sombre -->
    <div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
        <img 
            src="<?= escape_html($quality['image_bg']) ?>" 
            alt="<?= escape_html($quality['image_bg_alt']) ?>" 
            class="w-full h-full object-cover object-center" 
            width="<?= (int)$quality['width'] ?>" 
            height="<?= (int)$quality['height'] ?>" 
            loading="lazy"
        >
    </div>

    <div class="relative z-10 max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Titre de la section -->
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-[#FFE500] block mb-2">
                Méthode opérationnelle
            </span>
            <h2 id="quality-heading" class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
                <?= escape_html($quality['title']) ?>
            </h2>
            <p class="text-base text-slate-300 leading-relaxed">
                <?= escape_html($quality['subtitle']) ?>
            </p>
        </div>

        <!-- Barre d'onglets accessible (WAI-ARIA Tabs Pattern) -->
        <div class="flex justify-center mb-8 sm:mb-12">
            <div class="inline-flex flex-wrap justify-center p-1.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 gap-1.5" role="tablist" aria-label="Étapes du suivi logistique" data-tabs>
                <?php foreach ($quality['tabs'] as $idx => $t): 
                    $isSelected = ($idx === 0);
                ?>
                    <button 
                        type="button" 
                        role="tab" 
                        class="tab-btn px-5 py-2.5 rounded-lg text-sm font-semibold transition-all focus-brand-dark" 
                        id="tab-btn-<?= escape_html($t['id']) ?>" 
                        aria-controls="panel-<?= escape_html($t['id']) ?>" 
                        aria-selected="<?= $isSelected ? 'true' : 'false' ?>" 
                        tabindex="<?= $isSelected ? '0' : '-1' ?>"
                    >
                        <?= escape_html($t['title']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panneaux d'onglets (Carte blanche flottante) -->
        <div class="max-w-4xl mx-auto">
            <?php foreach ($quality['tabs'] as $idx => $t): 
                $isSelected = ($idx === 0);
            ?>
                <div 
                    role="tabpanel" 
                    id="panel-<?= escape_html($t['id']) ?>" 
                    aria-labelledby="tab-btn-<?= escape_html($t['id']) ?>" 
                    class="tab-panel bg-white text-[#171717] rounded-2xl p-6 sm:p-10 lg:p-12 shadow-xl border border-white/20 transition-all"
                    <?= !$isSelected ? 'hidden' : '' ?>
                >
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-6 border-b border-stone-200">
                        <div>
                            <span class="inline-block bg-[#171717] text-[#FFE500] text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-md mb-3">
                                <?= escape_html($t['badge']) ?>
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight">
                                <?= escape_html($t['heading']) ?>
                            </h3>
                        </div>
                        <div class="shrink-0">
                            <a href="<?= escape_html($t['url']) ?>" class="inline-flex items-center gap-2 bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-5 py-2.5 rounded-lg shadow-sm transition-colors focus-brand-light">
                                <span><?= escape_html($t['link_label']) ?></span>
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="pt-6 space-y-6">
                        <p class="text-base sm:text-lg text-[#333333] leading-relaxed">
                            <?= escape_html($t['description']) ?>
                        </p>

                        <div class="bg-[#F4F3ED] rounded-xl p-5 border border-stone-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B] mb-3">
                                Points clés du traitement
                            </h4>
                            <ul class="space-y-2.5" aria-label="Points de la <?= escape_html($t['title']) ?>">
                                <?php foreach ($t['points'] as $pt): ?>
                                    <li class="flex items-center gap-3 text-sm sm:text-base text-[#171717]">
                                        <svg class="w-4 h-4 text-[#B5A000] shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><?= escape_html($pt) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
