<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/lib/security.php';

$ip = $_SERVER['REMOTE_ADDR'];
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    rate_limit_hit('contact', $ip, 5, 3600); // 5 requêtes par heure
    
    // Honeypot
    if (!empty($_POST['website'])) {
        // Robot détecté, on fait semblant que c'est bon
        $success = true;
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $settings = get_app_settings();
            $dest = $settings['email'];
            $apiKey = getenv('RESEND_API_KEY');
            $fromEmail = getenv('RESEND_FROM_EMAIL');
            $fromName = getenv('RESEND_FROM_NAME') ?: $settings['nom'];
            
            if (!$apiKey || !$dest || !$fromEmail) {
                // Pas configuré : on skip et on log
                $stmt = $pdo->prepare("INSERT INTO email_logs (recipient, subject, status, error_message) VALUES (?, ?, 'skipped', 'Configuration Resend manquante')");
                $stmt->execute([$dest ?: 'inconnu', "Nouveau message de $name"]);
                $success = true; // Neutre pour l'utilisateur
            } else {
                // Appel cURL à Resend
                $ch = curl_init('https://api.resend.com/emails');
                $payload = json_encode([
                    'from' => "$fromName <$fromEmail>",
                    'to' => [$dest],
                    'subject' => "Nouveau message de contact : " . $name,
                    'html' => "<p><strong>Nom :</strong> " . escape_html($name) . "</p>" .
                              "<p><strong>Email :</strong> " . escape_html($email) . "</p>" .
                              "<p><strong>Message :</strong><br>" . nl2br(escape_html($message)) . "</p>"
                ]);
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode >= 200 && $httpCode < 300) {
                    $pdo->prepare("INSERT INTO email_logs (recipient, subject, status) VALUES (?, ?, 'sent')")->execute([$dest, "Message de $name"]);
                    $success = true;
                } else {
                    $pdo->prepare("INSERT INTO email_logs (recipient, subject, status, error_message) VALUES (?, ?, 'failed', ?)")->execute([$dest, "Message de $name", $response]);
                    $error = "Une erreur est survenue lors de l'envoi. Veuillez réessayer plus tard.";
                }
            }
        } else {
            $error = "Veuillez remplir tous les champs correctement.";
        }
    }
}

require_once __DIR__ . '/templates/public_header.php';
?>

<main class="flex-grow flex flex-col py-16 px-4 bg-surface">
    <div class="max-w-xl mx-auto w-full">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            <h1 class="text-3xl font-bold text-primary mb-2">Contactez-nous</h1>
            <p class="text-slate-500 mb-8">Remplissez le formulaire ci-dessous, notre équipe vous répondra dans les plus brefs délais.</p>
            
            <?php if ($success): ?>
                <div class="bg-status-delivered-bg text-status-delivered-text p-4 rounded-lg border border-status-delivered-bg mb-6">
                    Votre message a bien été envoyé. Nous vous contacterons rapidement.
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-lg border border-red-100 mb-6">
                        <?= escape_html($error) ?>
                    </div>
                <?php endif; ?>
                
                <form action="/contact" method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
                    
                    <!-- Honeypot -->
                    <div style="display:none;" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nom complet</label>
                        <input type="text" name="name" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none" required value="<?= escape_html($_POST['name'] ?? '') ?>">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse e-mail</label>
                        <input type="email" name="email" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none" required value="<?= escape_html($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Message</label>
                        <textarea name="message" rows="5" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-none" required><?= escape_html($_POST['message'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-accent text-white font-bold py-3 rounded-lg hover:bg-amber-600 transition-colors shadow-sm">
                        Envoyer le message
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
