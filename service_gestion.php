<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col py-16 px-4 bg-surface">
    <div class="max-w-4xl mx-auto w-full">
        <a href="/" class="inline-flex items-center text-sm font-semibold text-slate-500 hover:text-primary transition-colors mb-8">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour à l'accueil
        </a>
        
        <div class="bg-white rounded-2xl shadow-elevation-1 border border-slate-200 overflow-hidden mb-12">
            <div class="p-8 md:p-12 border-b border-slate-100">
                <div class="w-16 h-16 rounded-xl bg-action/10 text-action flex items-center justify-center mb-8">
                    <svg class="w-8 h-8 animate-[gentleShake_4s_ease-in-out_infinite]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-6 tracking-tight">Gestion des expéditions</h1>
                <p class="text-lg text-slate-600 leading-relaxed max-w-2xl">
                    Une interface dédiée, sécurisée et professionnelle pour gérer vos colis, leurs statuts et leurs informations de suivi.
                </p>
            </div>
            
            <div class="p-8 md:p-12 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">La gestion des colis</h2>
                        <p class="text-slate-600 mb-6 leading-relaxed text-sm">
                            L'interface de gestion permet de regrouper toutes vos expéditions au sein d'un même espace de contrôle. Grâce à ce tableau de bord, vous obtenez une vue globale des activités logistiques de votre entreprise.
                        </p>
                        
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Ce que permet l'interface</h2>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">La consultation centralisée de tous les colis actifs</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">La modification et la mise à jour des expéditions</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">L'ajout d'événements de suivi précis avec géolocalisation indicative</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-6 tracking-tight">Gestion des statuts</h2>
                        <p class="text-slate-600 mb-6 leading-relaxed text-sm">
                            Afin d'assurer un suivi rigoureux, vous pouvez faire évoluer chaque colis à travers le cycle de livraison officiel :
                        </p>
                        <div class="relative pl-6 space-y-8 before:absolute before:inset-y-2 before:left-[11px] before:w-[2px] before:bg-slate-200">
                            <div class="relative">
                                <div class="absolute left-[-29px] top-1 w-3 h-3 rounded-full bg-slate-400"></div>
                                <h3 class="font-bold text-primary text-sm">1. Expédié</h3>
                            </div>
                            <div class="relative">
                                <div class="absolute left-[-29px] top-1 w-3 h-3 rounded-full bg-action shadow-[0_0_0_4px_rgba(23,92,211,0.1)]"></div>
                                <h3 class="font-bold text-primary text-sm">2. En cours de livraison</h3>
                            </div>
                            <div class="relative">
                                <div class="absolute left-[-29px] top-1 w-3 h-3 rounded-full bg-accent"></div>
                                <h3 class="font-bold text-primary text-sm">3. Livré</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 md:p-12 text-center border-t border-slate-100 bg-white">
                <a href="/track" class="btn-primary inline-flex text-lg w-full sm:w-auto">
                    Consulter le suivi d'un colis
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>
</main>

<style>
@keyframes gentleShake { 0%, 100% { transform: rotate(0deg); } 5% { transform: rotate(-3deg); } 10% { transform: rotate(3deg); } 15% { transform: rotate(0deg); } }
</style>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
