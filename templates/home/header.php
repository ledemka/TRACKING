<?php
// templates/home/header.php
// En-tête public fond noir #171717
$content = get_site_content();
$nomEntreprise = $content['brand']['nom'];

// Gestion du logo : téléversé dans /settings si présent, sinon fallback logo-mark-fond-sombre
$logoUploaded = !empty($settings['logo_url']) ? $settings['logo_url'] : null;
$logoPath = $logoUploaded ? $logoUploaded : '/assets/img/logo-mark-fond-sombre.svg';
?>
<header class="bg-[#171717] text-white border-b border-white/10 sticky top-0 z-50 transition-colors">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
        <!-- Logo + Nom -->
        <a href="/" class="flex items-center gap-3 text-white focus-brand-dark rounded-md py-1 px-1 transition-opacity hover:opacity-90">
            <img src="<?= escape_html($logoPath) ?>" alt="<?= escape_html($nomEntreprise) ?>" class="h-9 w-auto object-contain shrink-0" width="36" height="36">
            <span class="font-bold text-xl sm:text-2xl tracking-tight text-white"><?= escape_html($nomEntreprise) ?></span>
        </a>

        <!-- Navigation Desktop -->
        <nav class="hidden md:flex items-center gap-8" aria-label="Navigation principale">
            <?php foreach ($content['nav'] as $item): 
                $isActive = ($_SERVER['REQUEST_URI'] === $item['url'] || ($item['url'] !== '/' && strpos($_SERVER['REQUEST_URI'], $item['url']) === 0));
            ?>
                <a href="<?= escape_html($item['url']) ?>" class="font-medium text-sm transition-colors focus-brand-dark rounded px-2 py-1 <?= $isActive ? 'text-[#FFE500] font-semibold' : 'text-slate-200 hover:text-white' ?>">
                    <?= escape_html($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Bouton Action Desktop -->
        <div class="hidden md:flex items-center">
            <a href="/track" class="bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-5 py-2.5 rounded-lg shadow-sm transition-all focus-brand-dark flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Suivre un colis</span>
            </a>
        </div>

        <!-- Bouton Burger Mobile -->
        <div class="flex md:hidden items-center">
            <button id="mobile-menu-btn" type="button" class="p-2.5 rounded-lg text-slate-200 hover:text-white hover:bg-white/10 transition-colors focus-brand-dark" aria-label="Ouvrir le menu de navigation" aria-expanded="false" aria-controls="mobile-menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Menu Mobile Déroulant -->
    <div id="mobile-menu" class="hidden md:hidden bg-[#171717] border-b border-white/10 px-4 pt-3 pb-6 space-y-3">
        <nav class="flex flex-col space-y-1" aria-label="Navigation mobile">
            <?php foreach ($content['nav'] as $item): 
                $isActive = ($_SERVER['REQUEST_URI'] === $item['url'] || ($item['url'] !== '/' && strpos($_SERVER['REQUEST_URI'], $item['url']) === 0));
            ?>
                <a href="<?= escape_html($item['url']) ?>" class="px-3 py-2.5 rounded-lg text-base font-medium transition-colors focus-brand-dark <?= $isActive ? 'bg-white/10 text-[#FFE500] font-semibold' : 'text-slate-200 hover:bg-white/5 hover:text-white' ?>">
                    <?= escape_html($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="pt-2">
            <a href="/track" class="w-full bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-center px-4 py-3 rounded-lg shadow-sm transition-all focus-brand-dark flex items-center justify-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Suivre un colis</span>
            </a>
        </div>
    </div>
</header>
