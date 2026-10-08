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
                    <svg class="w-8 h-8 animate-[slideRight_2s_ease-in-out_infinite]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-6 tracking-tight">Livraison</h1>
                <p class="text-lg text-slate-600 leading-relaxed max-w-2xl">
                    Suivez l'avancement de votre colis jusqu'à sa livraison finale. Nous vous accompagnons jusqu'au dernier kilomètre.
                </p>
            </div>
            
            <div class="p-8 md:p-12 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Les dernières étapes du parcours</h2>
                        <p class="text-slate-600 mb-6 leading-relaxed text-sm">
                            L'étape de livraison est le moment où votre colis approche de sa destination. Dès que votre suivi indique le statut « En cours de livraison », cela signifie que les équipes logistiques locales ont pris le relais.
                        </p>
                        
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">La progression finale</h2>
                        <p class="text-slate-600 leading-relaxed mb-4 text-sm">
                            Une fois le colis arrivé :
                        </p>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">L'événement de livraison est enregistré</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">La chronologie du suivi est clôturée</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">Le statut final s'affiche sur votre espace</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Que signifie « Livré » ?</h2>
                        <div class="bg-white p-6 rounded-2xl shadow-elevation-1 border border-status-delivered-bg mb-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="px-3 py-1 bg-status-delivered-bg text-status-delivered-text rounded-full font-bold text-xs border border-status-delivered-bg">Livré</div>
                            </div>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Le statut strict « Livré » garantit que le colis a été remis au destinataire à la ville de destination prévue. Aucun événement supplémentaire ne sera ajouté par la suite.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 md:p-12 text-center border-t border-slate-100 bg-white">
                <a href="/track" class="btn-primary inline-flex text-lg w-full sm:w-auto">
                    Suivre une livraison
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>
</main>

<style>
@keyframes slideRight { 0% { transform: translateX(-3px); opacity: 0.9; } 50% { transform: translateX(3px); opacity: 1; } 100% { transform: translateX(-3px); opacity: 0.9; } }
</style>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
