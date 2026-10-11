<?php
require_once __DIR__ . '/api/bootstrap.php';
http_response_code(404);
$page_title = 'Page introuvable (404)';
$page_description = "La page demandée n'existe pas ou a été déplacée. Utilisez notre formulaire pour retrouver votre expédition.";
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => 'Référence introuvable',
    'title' => 'Page non trouvée (Erreur 404)',
    'description' => 'L\'adresse demandée n\'existe pas ou a été déplacée. Vous pouvez retourner à l\'accueil ou accéder directement au suivi de votre colis.',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => '/'],
        ['label' => 'Erreur 404']
    ],
    'actions' => [
        ['label' => 'Retourner à l\'accueil', 'url' => '/', 'primary' => true],
        ['label' => 'Suivre un colis', 'url' => '/track', 'primary' => false],
    ]
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <section class="py-16 sm:py-24 bg-[#F4F3ED] flex items-center justify-center">
        <div class="text-center max-w-lg mx-auto px-4">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white border border-stone-200 text-[#171717] mb-6 shadow-sm">
                <svg class="w-10 h-10 text-[#FFE500]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#171717] tracking-tight mb-4">
                Cette page n'est pas disponible
            </h2>
            <p class="text-base text-[#6B6B6B] leading-relaxed mb-8">
                Vérifiez l'URL saisie ou utilisez nos raccourcis pour reprendre votre navigation dans la plateforme de suivi.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/" class="inline-flex items-center gap-2 bg-[#171717] text-white hover:bg-black font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-dark shadow-sm">
                    Retour à l'accueil
                </a>
                <a href="/track" class="inline-flex items-center gap-2 bg-[#FFE500] text-[#171717] hover:bg-[#E6CE00] font-bold text-sm px-6 py-3.5 rounded-lg transition-colors focus-brand-light shadow-sm">
                    Suivre une expédition
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
