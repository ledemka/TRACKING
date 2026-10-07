<?php
// Layout public minimal pour la Phase 1
require_once __DIR__ . '/api/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de Colis - Phase 1</title>
    <!-- On chargera le CSS compilé ici -->
    <link rel="stylesheet" href="/public/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-surface min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 max-w-md w-full">
        <h1 class="text-2xl font-bold text-primary mb-4 text-center">Phase 1 : Socle technique prêt</h1>
        <p class="text-slate-500 text-sm mb-6 text-center">L'environnement PHP (BDD, sessions, sécurité) et la configuration Tailwind sont en place.</p>
        <div class="space-y-4">
            <div class="flex items-center gap-3 bg-status-shipped-bg p-3 rounded-md">
                <div class="w-2 h-2 rounded-full bg-status-shipped-dot"></div>
                <span class="text-status-shipped-text font-semibold text-sm">Design System chargé</span>
            </div>
            <button class="w-full bg-accent text-white font-semibold py-3 rounded-lg hover:bg-amber-600 transition-colors shadow-sm">
                Tester le bouton
            </button>
        </div>
    </div>
</body>
</html>
