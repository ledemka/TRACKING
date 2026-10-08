<?php
require_once __DIR__ . '/api/bootstrap.php';

if (is_logged_in()) {
    header("Location: /dashboard");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Session expirée ou invalide. Veuillez réessayer.";
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (login($email, $password)) {
            header("Location: /dashboard");
            exit;
        } else {
            $error = "Identifiants invalides."; // Message générique
        }
    }
}

require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex items-center justify-center py-20 px-4 bg-surface">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 max-w-md w-full">
        <h1 class="text-2xl font-bold text-primary mb-6 text-center">Connexion Espace Admin</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm mb-4 border border-red-100">
                <?= escape_html($error) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Mot de passe</label>
                <input type="password" name="password" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white font-semibold py-3 rounded-lg hover:bg-slate-800 transition-colors shadow-sm mt-4">
                Se connecter
            </button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
