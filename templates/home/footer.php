<?php
// templates/home/footer.php
// Pied de page noir #171717 en 3 colonnes
$content = get_site_content();
$nomEntreprise = $content['brand']['nom'];

// Gestion du logo : logo personnalisé si présent, sinon fallback logo-mark-fond-sombre
$logoUploaded = !empty($settings['logo_url']) ? $settings['logo_url'] : null;
$logoPath = $logoUploaded ? $logoUploaded : '/assets/img/logo-mark-fond-sombre.svg';
?>
<footer class="bg-[#171717] text-white border-t-4 border-[#FFE500] pt-16 pb-12 mt-auto">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 pb-12 border-b border-white/10">
            <!-- Colonne 1 : Logo + Présentation -->
            <div class="md:col-span-5 space-y-4">
                <a href="/" class="flex items-center gap-3 text-white focus-brand-dark rounded-md transition-opacity hover:opacity-90 inline-flex">
                    <img src="<?= escape_html($logoPath) ?>" alt="<?= escape_html($nomEntreprise) ?>" class="h-9 w-auto object-contain shrink-0" width="36" height="36">
                    <span class="font-bold text-xl sm:text-2xl tracking-tight text-white"><?= escape_html($nomEntreprise) ?></span>
                </a>
                <p class="text-sm text-slate-300 leading-relaxed max-w-sm">
                    <?= escape_html($content['brand']['description']) ?>
                </p>
            </div>

            <!-- Colonne 2 : Liens rapides -->
            <div class="md:col-span-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#FFE500] mb-4">
                    Liens rapides
                </h3>
                <ul class="space-y-2.5 text-sm" aria-label="Liens rapides">
                    <?php foreach ($content['footer']['quick_links'] as $link): ?>
                        <li>
                            <a href="<?= escape_html($link['url']) ?>" class="text-slate-300 hover:text-white transition-colors focus-brand-dark rounded px-1 py-0.5 inline-block">
                                <?= escape_html($link['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Colonne 3 : Coordonnées de contact -->
            <div class="md:col-span-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#FFE500] mb-4">
                    Contact
                </h3>
                <ul class="space-y-3 text-sm text-slate-300" aria-label="Coordonnées de l'entreprise">
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-[#FFE500] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span><?= escape_html($content['contact']['adresse']) ?></span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#FFE500] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?php if ($content['contact']['email'] !== '[À compléter]'): ?>
                            <a href="mailto:<?= escape_html($content['contact']['email']) ?>" class="hover:text-white transition-colors focus-brand-dark">
                                <?= escape_html($content['contact']['email']) ?>
                            </a>
                        <?php else: ?>
                            <span><?= escape_html($content['contact']['email']) ?></span>
                        <?php endif; ?>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#FFE500] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <?php if ($content['contact']['telephone'] !== '[À compléter]'): ?>
                            <a href="tel:<?= escape_html($content['contact']['telephone']) ?>" class="hover:text-white transition-colors focus-brand-dark">
                                <?= escape_html($content['contact']['telephone']) ?>
                            </a>
                        <?php else: ?>
                            <span><?= escape_html($content['contact']['telephone']) ?></span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright dynamique -->
        <div class="pt-8 text-center text-xs text-slate-400">
            <p>
                &copy; <?= date('Y') ?> <?= escape_html($nomEntreprise) ?>. Tous droits réservés.
            </p>
        </div>
    </div>
</footer>
