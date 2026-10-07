<?php
require_once __DIR__ . '/api/bootstrap.php';

$ip = $_SERVER['REMOTE_ADDR'];
$success = false;
$error = '';
$settings = get_app_settings();

if (isset($_SESSION['flash_success'])) {
    $success = true;
    unset($_SESSION['flash_success']);
}

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
        
        if (strlen($name) > 100 || strlen($email) > 254 || strlen($message) > 5000) {
            $error = "La taille d'un champ dépasse la limite autorisée.";
        } else if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $dest = $settings['email'];
            $apiKey = getenv('RESEND_API_KEY');
            $fromEmail = getenv('RESEND_FROM_EMAIL');
            $fromName = getenv('RESEND_FROM_NAME') ?: $settings['nom'];
            
            if (!$apiKey || !$dest || !$fromEmail) {
                // Pas configuré : on skip et on log
                $subjectLog = substr("Nouveau message de $name", 0, 200);
                $stmt = $pdo->prepare("INSERT INTO email_logs (recipient, subject, status, error_message) VALUES (?, ?, 'skipped', 'Configuration Resend manquante')");
                $stmt->execute([$dest ?: 'inconnu', $subjectLog]);
                $_SESSION['flash_success'] = true;
                header('Location: /contact');
                exit;
            } else {
                $subject = "Nouveau message de contact : " . $name;
                $subjectLog = substr($subject, 0, 200);
                
                // Appel cURL à Resend
                $ch = curl_init('https://api.resend.com/emails');
                $payload = json_encode([
                    'from' => "$fromName <$fromEmail>",
                    'to' => [$dest],
                    'reply_to' => $email,
                    'subject' => $subject,
                    'html' => "<p><strong>Nom :</strong> " . escape_html($name) . "</p>" .
                              "<p><strong>Email :</strong> " . escape_html($email) . "</p>" .
                              "<p><strong>Message :</strong><br>" . nl2br(escape_html($message)) . "</p>"
                ]);
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode >= 200 && $httpCode < 300) {
                    $pdo->prepare("INSERT INTO email_logs (recipient, subject, status) VALUES (?, ?, 'sent')")->execute([$dest, $subjectLog]);
                    $_SESSION['flash_success'] = true;
                    header('Location: /contact');
                    exit;
                } else {
                    $errorMsg = substr($httpCode . " " . $response, 0, 500);
                    $pdo->prepare("INSERT INTO email_logs (recipient, subject, status, error_message) VALUES (?, ?, 'failed', ?)")->execute([$dest, $subjectLog, $errorMsg]);
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

<main class="flex-grow flex flex-col py-16 px-4 bg-surface transition-opacity duration-500">
    <div class="max-w-4xl mx-auto w-full flex flex-col md:flex-row gap-12">
        
        <?php if (!empty($settings['email']) || !empty($settings['telephone']) || !empty($settings['adresse'])): ?>
        <div class="w-full md:w-1/3 flex flex-col gap-6">
            <div>
                <h1 class="text-3xl font-bold text-primary mb-2">Contact</h1>
                <p class="text-slate-500">N'hésitez pas à nous joindre, notre équipe est là pour vous aider.</p>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col gap-4">
                <?php if (!empty($settings['email'])): ?>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-surface rounded-xl flex items-center justify-center text-action flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-slate-700">Email</span>
                        <a href="mailto:<?= escape_html($settings['email']) ?>" class="text-slate-500 hover:text-action transition-colors"><?= escape_html($settings['email']) ?></a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['telephone'])): ?>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-surface rounded-xl flex items-center justify-center text-action flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-slate-700">Téléphone</span>
                        <a href="tel:<?= escape_html($settings['telephone']) ?>" class="text-slate-500 hover:text-action transition-colors"><?= escape_html($settings['telephone']) ?></a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($settings['adresse'])): ?>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-surface rounded-xl flex items-center justify-center text-action flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-slate-700">Adresse</span>
                        <span class="text-slate-500"><?= nl2br(escape_html($settings['adresse'])) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-full md:w-2/3">
        <?php else: ?>
        <div class="max-w-xl mx-auto w-full">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-primary mb-2">Contactez-nous</h1>
                <p class="text-slate-500">Remplissez le formulaire ci-dessous, notre équipe vous répondra dans les plus brefs délais.</p>
            </div>
        <?php endif; ?>
        
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300">
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
                            <input type="text" name="name" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-action focus:ring-1 focus:ring-action outline-none transition-colors" required value="<?= escape_html($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Adresse e-mail</label>
                            <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-action focus:ring-1 focus:ring-action outline-none transition-colors" required value="<?= escape_html($_POST['email'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Message</label>
                            <textarea name="message" rows="5" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-action focus:ring-1 focus:ring-action outline-none resize-none transition-colors" required><?= escape_html($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-action text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                            Envoyer le message
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
