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
                    <svg class="w-8 h-8 animate-[floatBox_2s_ease-in-out_infinite_alternate]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-6 tracking-tight">Expédition</h1>
                <p class="text-lg text-slate-600 leading-relaxed max-w-2xl">
                    Préparez et expédiez vos colis simplement avec un suivi à chaque étape. Ce service est le point de départ de la logistique de vos colis.
                </p>
            </div>
            
            <div class="p-8 md:p-12 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Le rôle du service</h2>
                        <p class="text-slate-600 mb-6 leading-relaxed text-sm">
                            L'expédition est l'étape initiale et cruciale du parcours de votre colis. Elle permet d'enregistrer toutes les informations nécessaires au bon acheminement de votre marchandise.
                        </p>
                        
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Comment préparer une expédition ?</h2>
                        <p class="text-slate-600 leading-relaxed mb-4 text-sm">
                            Pour garantir une livraison sans accroc, il est important de fournir des informations précises :
                        </p>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">Nom du destinataire clair</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">Adresse de départ et d'arrivée</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">Sélection du transporteur</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-6 tracking-tight">Les étapes principales</h2>
                        <div class="relative pl-6 space-y-8 before:absolute before:inset-y-2 before:left-[11px] before:w-[2px] before:bg-slate-200">
                            <div class="relative">
                                <div class="absolute left-[-29px] top-1 w-3 h-3 rounded-full bg-accent shadow-[0_0_0_4px_rgba(245,158,11,0.15)]"></div>
                                <h3 class="font-bold text-primary text-sm">Création</h3>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Rendez-vous dans votre agence pour initier l'expédition.</p>
                            </div>
                            <div class="relative">
                                <div class="absolute left-[-29px] top-1 w-3 h-3 rounded-full bg-slate-300"></div>
                                <h3 class="font-bold text-primary text-sm">Génération du suivi</h3>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Un numéro unique est généré pour le colis.</p>
                            </div>
                            <div class="relative">
                                <div class="absolute left-[-29px] top-1 w-3 h-3 rounded-full bg-slate-300"></div>
                                <h3 class="font-bold text-primary text-sm">Prise en charge</h3>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Le colis passe au statut "Expédié" et le suivi public s'active.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 md:p-12 text-center border-t border-slate-100 bg-white">
                <a href="/track" class="btn-primary inline-flex text-lg w-full sm:w-auto">
                    En savoir plus sur le suivi
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>
</main>

<style>
@keyframes floatBox { 0% { transform: translateY(2px); } 100% { transform: translateY(-2px); } }
</style>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
