<?php
require_once __DIR__ . '/api/bootstrap.php';
require_admin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    if (!$nom) {
        $error = "Le nom de l'entreprise est obligatoire.";
    } else {
        $stmt = $pdo->prepare("UPDATE app_settings SET nom = ?, email = ?, telephone = ?, adresse = ? WHERE id = (SELECT MIN(id) FROM app_settings)");
        $stmt->execute([$nom, $email, $telephone, $adresse]);
        $success = "Paramètres mis à jour avec succès.";
        
        // Refresh settings variable for layout
        $stmt = $pdo->query("SELECT * FROM app_settings LIMIT 1");
        $settings = $stmt->fetch();
    }
}

$page_title = 'Administration';
require_once __DIR__ . '/templates/admin_layout.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-primary">Paramètres de l'application</h1>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 border border-green-100">
        <?= escape_html($success) ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 border border-red-100">
        <?= escape_html($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 max-w-2xl">
    <form method="POST" action="/settings" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nom de l'entreprise *</label>
            <input type="text" name="nom" required value="<?= escape_html($settings['nom']) ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            <p class="text-xs text-slate-500 mt-1">Sera affiché dans l'en-tête et les courriels.</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">E-mail de contact public</label>
            <input type="email" name="email" value="<?= escape_html($settings['email'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            <p class="text-xs text-slate-500 mt-1">Utilisé comme destinataire du formulaire de contact.</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone public</label>
            <input type="text" name="telephone" value="<?= escape_html($settings['telephone'] ?? '') ?>" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse complète</label>
            <textarea name="adresse" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none"><?= escape_html($settings['adresse'] ?? '') ?></textarea>
        </div>
        
        <div class="pt-4 border-t border-slate-100">
            <button type="submit" class="bg-primary text-white px-8 py-3 rounded-lg font-bold hover:bg-slate-800 transition-colors">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/admin_footer.php'; ?>
