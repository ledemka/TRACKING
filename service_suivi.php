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
                    <svg class="w-8 h-8 animate-[pulseTimeline_2.5s_cubic-bezier(0.4,0,0.6,1)_infinite]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-primary mb-6 tracking-tight">Suivi des colis</h1>
                <p class="text-lg text-slate-600 leading-relaxed max-w-2xl">
                    Consultez l'état de votre expédition à l'aide de votre numéro de suivi. Un historique des événements de livraison.
                </p>
            </div>
            
            <div class="p-8 md:p-12 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Fonctionnement du suivi</h2>
                        <p class="text-slate-600 mb-6 leading-relaxed text-sm">
                            Chaque colis expédié dispose d'un identifiant unique (numéro de suivi) généré automatiquement. Il est la clé pour retrouver toutes les informations de votre expédition sur notre plateforme publique.
                        </p>
                        
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Comment retrouver une expédition ?</h2>
                        <p class="text-slate-600 leading-relaxed mb-4 text-sm">
                            Saisissez simplement votre numéro dans la barre de recherche. L'interface de suivi s'affiche et vous permet de consulter :
                        </p>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">L'état global ("Expédié", "En cours", "Livré")</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">La chronologie complète des événements</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                                <span class="text-slate-700 text-sm font-medium">La date de livraison estimée</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <div class="bg-white p-6 rounded-2xl shadow-elevation-1 border border-slate-200 mb-6">
                            <h3 class="font-bold text-primary mb-2 text-sm">Respect de la confidentialité</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Afin de garantir la protection des données personnelles, l'interface publique anonymise le nom du destinataire et masque l'adresse complète. Le suivi est conçu pour informer sans divulguer de données sensibles.
                            </p>
                        </div>
                        
                        <h2 class="text-xl font-bold text-primary mb-4 tracking-tight">Comprendre l'état</h2>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="px-3 py-1 bg-status-shipped-bg text-status-shipped-text rounded-full font-bold text-xs border border-status-shipped-bg">Expédié</div>
                                <span class="text-sm text-slate-600">Le colis a été pris en charge.</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="px-3 py-1 bg-status-delivery-bg text-status-delivery-text rounded-full font-bold text-xs border border-status-delivery-bg">En cours</div>
                                <span class="text-sm text-slate-600">Le colis est en cours de livraison vers sa destination.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 md:p-12 text-center border-t border-slate-100 bg-white">
                <a href="/track" class="btn-primary inline-flex text-lg w-full sm:w-auto">
                    Suivre un colis
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </div>
</main>

<style>
@keyframes pulseTimeline { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.6; transform: scale(0.95); } }
</style>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
