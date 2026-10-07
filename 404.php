<?php
require_once __DIR__ . '/api/bootstrap.php';
http_response_code(404);
require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex items-center justify-center py-20 px-4 bg-surface">
    <div class="text-center max-w-md">
        <div class="text-6xl mb-4">📦</div>
        <h1 class="text-4xl font-bold text-primary mb-4">Erreur 404</h1>
        <p class="text-slate-500 mb-8">La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="/" class="inline-block bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-slate-800 transition-colors">
            Retour à l'accueil
        </a>
    </div>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
