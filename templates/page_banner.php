<?php
/**
 * templates/page_banner.php
 * Composant de bandeau réutilisable pour les pages secondaires publiques.
 *
 * Paramètres attendus (ou passés via tableau $banner_config) :
 * - $banner_badge : texte du badge supérieur (ex. "Offre de services")
 * - $banner_title : titre H1 principal (ex. "Nos services d'acheminement...")
 * - $banner_description : paragraphe d'introduction
 * - $banner_breadcrumbs : tableau d'éléments [['label' => 'Accueil', 'url' => '/'], ['label' => 'Services']]
 * - $banner_actions : tableau optionnel de boutons [['label' => '...', 'url' => '...', 'primary' => true/false]]
 */

$cfg_badge = $banner_badge ?? ($banner_config['badge'] ?? '');
$cfg_title = $banner_title ?? ($banner_config['title'] ?? '');
$cfg_desc = $banner_description ?? ($banner_config['description'] ?? '');
$cfg_crumbs = $banner_breadcrumbs ?? ($banner_config['breadcrumbs'] ?? []);
$cfg_actions = $banner_actions ?? ($banner_config['actions'] ?? []);
?>

<?php if (!empty($cfg_crumbs)): ?>
    <!-- Fil d'Ariane épuré -->
    <nav class="bg-[#F4F3ED] border-b border-stone-200 py-3 px-4 sm:px-6 lg:px-8" aria-label="Fil d'Ariane">
        <div class="max-w-[1280px] mx-auto flex items-center gap-2 text-xs font-semibold text-[#6B6B6B]">
            <?php foreach ($cfg_crumbs as $index => $crumb): ?>
                <?php if ($index > 0): ?>
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                <?php endif; ?>
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?= escape_html($crumb['url']) ?>" class="hover:text-[#171717] transition-colors focus-brand-light rounded px-1 py-0.5">
                        <?= escape_html($crumb['label']) ?>
                    </a>
                <?php else: ?>
                    <span class="text-[#171717] font-bold" aria-current="page">
                        <?= escape_html($crumb['label']) ?>
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </nav>
<?php endif; ?>

<!-- Grand bandeau olive avec titre sombre #171717 -->
<section class="py-12 sm:py-16 lg:py-20 bg-[#B5A000] text-[#171717] relative overflow-hidden border-b border-black/10" aria-labelledby="banner-main-title">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl">
            <?php if (!empty($cfg_badge)): ?>
                <span class="inline-block bg-[#171717] text-[#FFE500] text-xs font-extrabold uppercase tracking-wider px-3 py-1.5 rounded-md mb-4 shadow-sm">
                    <?= escape_html($cfg_badge) ?>
                </span>
            <?php endif; ?>

            <h1 id="banner-main-title" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#171717] tracking-tight mb-6">
                <?= escape_html($cfg_title) ?>
            </h1>

            <?php if (!empty($cfg_desc)): ?>
                <p class="text-base sm:text-lg text-[#171717]/90 leading-relaxed font-medium mb-8 max-w-2xl">
                    <?= escape_html($cfg_desc) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($cfg_actions)): ?>
                <div class="flex flex-wrap gap-4">
                    <?php foreach ($cfg_actions as $action): ?>
                        <?php if (!empty($action['primary'])): ?>
                            <a href="<?= escape_html($action['url']) ?>" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-dark shadow-sm">
                                <span><?= escape_html($action['label']) ?></span>
                                <svg class="w-4 h-4 shrink-0 text-[#FFE500]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <a href="<?= escape_html($action['url']) ?>" class="inline-flex items-center gap-2 bg-white/20 text-[#171717] border border-[#171717]/20 hover:bg-white/40 font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-light">
                                <span><?= escape_html($action['label']) ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
