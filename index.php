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

    <!-- Services -->
    <section id="services" class="py-20 px-4 bg-white border-y border-slate-100">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-surface p-8 rounded-2xl shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center mb-6 text-action">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-primary mb-3">Expédition</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Préparez et expédiez vos colis simplement avec un suivi à chaque étape.</p>
                </div>
                <div class="bg-surface p-8 rounded-2xl shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center mb-6 text-action">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-primary mb-3">Suivi des colis</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Consultez l'état de votre expédition à l'aide de votre numéro de suivi.</p>
                </div>
                <div class="bg-surface p-8 rounded-2xl shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center mb-6 text-action">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-primary mb-3">Livraison</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Suivez l'avancement de votre colis jusqu'à sa livraison.</p>
                </div>
                <div class="bg-surface p-8 rounded-2xl shadow-sm border border-slate-100 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center mb-6 text-action">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-primary mb-3">Gestion des expéditions</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Une interface dédiée pour gérer vos colis et leurs informations de suivi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche ? -->
    <section class="py-20 px-4 bg-surface">
        <div class="max-w-4xl mx-auto">
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
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center gap-12">
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
                            <div class="h-4 w-24 bg-slate-200 rounded mb-2"></div>
                            <div class="h-3 w-32 bg-slate-100 rounded"></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-50 flex gap-4 items-center">
                        <div class="w-10 h-10 rounded-full bg-status-delivery-bg flex-shrink-0 flex items-center justify-center text-status-delivery-text">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div>
                            <div class="h-4 w-28 bg-slate-200 rounded mb-2"></div>
                            <div class="h-3 w-40 bg-slate-100 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
