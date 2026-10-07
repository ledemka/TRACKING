<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col">
    <!-- Hero Section -->
    <section class="bg-surface py-20 px-4 transition-opacity duration-500">
        <div class="max-w-2xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-primary mb-6">Suivez vos colis en toute simplicité.</h1>
            <p class="text-lg text-slate-500 mb-10">Consultez l'état de votre expédition en entrant votre numéro de suivi ci-dessous.</p>
            
            <form action="/track" method="GET" class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                <input type="text" name="id" placeholder="Numéro de suivi" class="flex-grow px-5 py-4 rounded-xl border border-slate-300 focus:border-action focus:ring-2 focus:ring-action/20 outline-none code-tracking text-lg shadow-sm transition-shadow" required>
                <button type="submit" class="bg-action text-white px-8 py-4 rounded-xl font-bold hover:bg-blue-700 transition-colors whitespace-nowrap shadow-sm text-lg">
                    Rechercher
                </button>
            </form>
        </div>
    </section>

    <!-- Services Interactive Component -->
    <section id="services" class="py-24 px-4 bg-white border-y border-slate-100 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-primary mb-16">Nos services logistiques</h2>
            
            <style>
            @media (prefers-reduced-motion: no-preference) {
                .service-card.active[data-index="0"] svg { animation: floatBox 2s ease-in-out infinite alternate; }
                .service-card.active[data-index="1"] svg { animation: pulseTimeline 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
                .service-card.active[data-index="2"] svg { animation: slideRight 2s ease-in-out infinite; }
                .service-card.active[data-index="3"] svg { animation: gentleShake 4s ease-in-out infinite; }
            }
            @keyframes floatBox { 0% { transform: translateY(3px); } 100% { transform: translateY(-3px); } }
            @keyframes pulseTimeline { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.6; transform: scale(0.95); } }
            @keyframes slideRight { 0% { transform: translateX(-3px); opacity: 0.9; } 50% { transform: translateX(3px); opacity: 1; } 100% { transform: translateX(-3px); opacity: 0.9; } }
            @keyframes gentleShake { 0%, 100% { transform: rotate(0deg); } 5% { transform: rotate(-3deg); } 10% { transform: rotate(3deg); } 15% { transform: rotate(0deg); } }
            </style>

            <div class="relative flex flex-col md:flex-row gap-4 md:gap-6 md:min-h-[420px]" id="services-container" role="tablist" aria-orientation="horizontal">
                <!-- Ligne de connexion desktop -->
                <div class="hidden md:block absolute top-[4.5rem] left-[10%] right-[10%] h-0.5 bg-slate-100 z-0"></div>

                <?php 
                $services = [
                    [
                        'title' => 'Expédition',
                        'desc' => 'Préparez et expédiez vos colis simplement avec un suivi à chaque étape.',
                        'url' => '/services/expedition',
                        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
                        'id' => 'expedition',
                        'num' => '01'
                    ],
                    [
                        'title' => 'Suivi des colis',
                        'desc' => 'Consultez l\'état de votre expédition à l\'aide de votre numéro de suivi.',
                        'url' => '/services/suivi',
                        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
                        'id' => 'suivi',
                        'num' => '02'
                    ],
                    [
                        'title' => 'Livraison',
                        'desc' => 'Suivez l\'avancement de votre colis jusqu\'à sa livraison.',
                        'url' => '/services/livraison',
                        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
                        'id' => 'livraison',
                        'num' => '03'
                    ],
                    [
                        'title' => 'Gestion des expéditions',
                        'desc' => 'Une interface dédiée pour gérer vos colis et leurs informations de suivi.',
                        'url' => '/services/gestion-expeditions',
                        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>',
                        'id' => 'gestion',
                        'num' => '04'
                    ]
                ];
                ?>

                <?php foreach($services as $index => $srv): ?>
                <button type="button" 
                        role="tab"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                        class="service-card group relative z-10 flex flex-col bg-surface border border-slate-200 rounded-3xl p-6 md:p-8 text-left transition-all duration-500 ease-in-out cursor-pointer focus:outline-none focus:ring-2 focus:ring-action/50 overflow-hidden <?= $index === 0 ? 'active shadow-md border-action/20' : 'hover:bg-slate-50 opacity-80 hover:opacity-100 shadow-sm' ?>"
                        style="flex: <?= $index === 0 ? '3 1 0%' : '1 1 0%' ?>;"
                        data-index="<?= $index ?>">
                    
                    <!-- Ligne horizontale interne pour mobile (connexion visuelle) -->
                    <?php if($index > 0): ?>
                    <div class="md:hidden absolute -top-4 left-1/2 w-0.5 h-8 bg-slate-100 -translate-x-1/2 z-0"></div>
                    <?php endif; ?>

                    <div class="flex flex-row md:flex-col items-center md:items-start justify-between md:justify-start gap-4 mb-4 md:mb-8 w-full z-10">
                        <span class="text-sm font-bold text-slate-400 group-[.active]:text-accent transition-colors order-2 md:order-1"><?= $srv['num'] ?></span>
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 group-[.active]:bg-action group-[.active]:text-white group-[.active]:scale-110 group-[.active]:shadow-lg group-[.active]:shadow-action/25 bg-white text-slate-400 border border-slate-200 group-[.active]:border-transparent group-hover:text-action order-1 md:order-2">
                            <?= $srv['icon'] ?>
                        </div>
                    </div>

                    <div class="flex-grow flex flex-col justify-end w-full z-10">
                        <h3 class="text-xl md:text-lg lg:text-xl font-bold text-primary group-[.active]:text-action group-[.active]:md:text-2xl transition-all duration-500 mb-0 group-[.active]:mb-4 leading-tight whitespace-nowrap md:whitespace-normal"><?= $srv['title'] ?></h3>
                        
                        <div class="service-content transition-all duration-500 ease-in-out overflow-hidden <?= $index === 0 ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0' ?>">
                            <div class="pt-2">
                                <p class="text-slate-500 text-sm md:text-base leading-relaxed mb-6">
                                    <?= $srv['desc'] ?>
                                </p>
                                <a href="<?= $srv['url'] ?>" class="inline-flex items-center justify-center bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-slate-800 transition-colors shadow-sm w-full sm:w-auto" <?= $index !== 0 ? 'tabindex="-1"' : '' ?>>
                                    Y aller
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.service-card');
        let currentIndex = 0;
        let autoRotateInterval;
        
        const activateCard = (targetCard) => {
            if(targetCard.classList.contains('active')) return;
            
            // Désactiver toutes les cartes
            cards.forEach(c => {
                c.classList.remove('active', 'shadow-md', 'border-action/20');
                c.classList.add('hover:bg-slate-50', 'opacity-80', 'shadow-sm');
                c.setAttribute('aria-selected', 'false');
                c.style.flex = '1 1 0%';
                
                const content = c.querySelector('.service-content');
                content.classList.remove('max-h-[500px]', 'opacity-100');
                content.classList.add('max-h-0', 'opacity-0');
                
                const link = c.querySelector('a');
                if(link) link.setAttribute('tabindex', '-1');
            });
            
            // Activer la carte courante
            targetCard.classList.add('active', 'shadow-md', 'border-action/20');
            targetCard.classList.remove('hover:bg-slate-50', 'opacity-80', 'shadow-sm');
            targetCard.setAttribute('aria-selected', 'true');
            targetCard.style.flex = '3 1 0%';
            
            const content = targetCard.querySelector('.service-content');
            content.classList.remove('max-h-0', 'opacity-0');
            content.classList.add('max-h-[500px]', 'opacity-100');
            
            const link = targetCard.querySelector('a');
            if(link) link.removeAttribute('tabindex');
        };

        const startAutoRotate = () => {
            stopAutoRotate();
            autoRotateInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % cards.length;
                activateCard(cards[currentIndex]);
            }, 5000);
        };

        const stopAutoRotate = () => {
            if (autoRotateInterval) clearInterval(autoRotateInterval);
        };

        cards.forEach((card, index) => {
            const handleInteraction = () => {
                currentIndex = index;
                activateCard(card);
                stopAutoRotate(); // On arrête la rotation si l'utilisateur interagit
            };
            
            // Reprendre la rotation quand on quitte la section
            card.addEventListener('mouseleave', startAutoRotate);
            
            // Support Hover (Desktop)
            card.addEventListener('mouseenter', handleInteraction);
            
            // Support Tap (Mobile)
            card.addEventListener('click', handleInteraction);
            
            // Support clavier (Focus/Entrée/Espace)
            card.addEventListener('focus', handleInteraction);
        });

        // Démarrer la rotation auto
        startAutoRotate();
    });
    </script>

    <!-- Comment ça marche ? -->
    <section class="py-20 px-4 bg-surface">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-primary mb-16">Comment ça marche ?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                <div class="hidden md:block absolute top-6 left-1/6 right-1/6 h-0.5 bg-slate-200 z-0"></div>
                <div class="text-center relative z-10">
                    <div class="w-12 h-12 mx-auto bg-action text-white rounded-full flex items-center justify-center font-bold text-lg mb-6 shadow-sm">
                        01
                    </div>
                    <h3 class="font-bold text-lg text-primary mb-3">Expédiez</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Votre colis est enregistré et préparé pour son expédition.</p>
                </div>
                <div class="text-center relative z-10">
                    <div class="w-12 h-12 mx-auto bg-action text-white rounded-full flex items-center justify-center font-bold text-lg mb-6 shadow-sm">
                        02
                    </div>
                    <h3 class="font-bold text-lg text-primary mb-3">Suivez</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Consultez son statut à tout moment avec votre numéro de suivi.</p>
                </div>
                <div class="text-center relative z-10">
                    <div class="w-12 h-12 mx-auto bg-action text-white rounded-full flex items-center justify-center font-bold text-lg mb-6 shadow-sm">
                        03
                    </div>
                    <h3 class="font-bold text-lg text-primary mb-3">Recevez</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Suivez les dernières étapes jusqu'à la livraison de votre colis.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Confiance -->
    <section class="py-20 px-4 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-12 lg:gap-24">
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-3xl font-bold text-primary mb-6">Un suivi clair à chaque étape</h2>
                <p class="text-lg text-slate-500 mb-8 leading-relaxed">
                    Nous vous permettons de consulter facilement l'avancement de vos expéditions, depuis leur expédition jusqu'à leur livraison.
                </p>
                <ul class="space-y-4 text-left inline-block md:block mx-auto">
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-slate-700 font-medium">Suivi simple</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-slate-700 font-medium">Informations sécurisées</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-slate-700 font-medium">Accès rapide</span>
                    </li>
                </ul>
            </div>
            <div class="flex-1 w-full bg-surface rounded-3xl p-8 border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-action/5 rounded-full blur-3xl"></div>
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-50 flex gap-4 items-center animate-[pulse_3s_ease-in-out_infinite]">
                        <div class="w-10 h-10 rounded-full bg-status-shipped-bg flex-shrink-0 flex items-center justify-center text-status-shipped-text">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800 mb-1">Colis pris en charge</div>
                            <div class="text-xs text-slate-500">Aujourd'hui, 08:30 &bull; Paris, FR</div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-50 flex gap-4 items-center">
                        <div class="w-10 h-10 rounded-full bg-status-delivery-bg flex-shrink-0 flex items-center justify-center text-status-delivery-text">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800 mb-1">En cours de livraison</div>
                            <div class="text-xs text-slate-500">Aujourd'hui, 14:15 &bull; En transit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
