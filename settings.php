<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/lib/auth.php';

require_admin();

$page_title = "Paramètres";
require __DIR__ . '/templates/admin_header.php';
?>

<div class="bg-white p-6 rounded shadow max-w-2xl mx-auto mt-8 text-center">
    <span class="material-symbols-outlined text-6xl text-slate-400 mb-4">settings</span>
    <h1 class="text-2xl font-bold mb-2">Paramètres</h1>
    <p class="text-slate-600">Disponible en phase 4</p>
</div>

<?php require __DIR__ . '/templates/admin_footer.php'; ?>
