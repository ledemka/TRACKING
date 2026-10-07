<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col">
    <!-- Hero Section -->
    <section class="bg-surface py-20 px-4">
        <div class="max-w-2xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-primary mb-6">Suivez votre colis en temps réel</h1>
            <p class="text-lg text-slate-500 mb-10">Entrez votre numéro de suivi ci-dessous pour connaître l'état de votre livraison.</p>
            
            <form action="/track" method="GET" class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                <input type="text" name="id" placeholder="Ex: COLIS-2026-00125" class="flex-grow px-5 py-4 rounded-xl border border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none code-tracking text-lg shadow-sm" required>
                <button type="submit" class="bg-accent text-white px-8 py-4 rounded-xl font-bold hover:bg-amber-600 transition-colors whitespace-nowrap shadow-sm text-lg">
                    Rechercher
                </button>
            </form>
        </div>
    </section>

    <!-- Étapes logistiques -->
    <section class="py-16 px-4 bg-white border-y border-slate-100">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-center text-primary mb-12">Les étapes de votre livraison</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-status-shipped-bg text-status-shipped-text rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Expédié</h3>
                    <p class="text-slate-500 text-sm">Votre colis a quitté l'entrepôt et est en route.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-status-delivery-bg text-status-delivery-text rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">En cours</h3>
                    <p class="text-slate-500 text-sm">Le livreur est en chemin vers votre adresse.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-status-delivered-bg text-status-delivered-text rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Livré</h3>
                    <p class="text-slate-500 text-sm">Le colis vous a été remis en main propre.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section id="services" class="py-20 px-4 bg-surface">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-center text-primary mb-12">Nos services logistiques</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-primary mb-3">Transport Express</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">[À compléter] Description générique du service de transport rapide proposé par l'entreprise.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-primary mb-3">Fret International</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">[À compléter] Description du service d'expédition au-delà des frontières, douanes incluses.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-primary mb-3">Logistique B2B</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">[À compléter] Solutions adaptées pour les professionnels et gestion des flux supply chain.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="py-16 px-4 bg-primary text-white text-center">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold mb-4">Une question sur votre expédition ?</h2>
            <p class="text-white/80 mb-8">Notre équipe est à votre disposition pour vous accompagner.</p>
            <a href="/contact" class="inline-block bg-accent text-white px-8 py-3 rounded-xl font-semibold hover:bg-amber-600 transition-colors shadow-sm">
                Nous contacter
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
