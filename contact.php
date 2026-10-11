<?php
require_once __DIR__ . '/api/bootstrap.php';

$ip = $_SERVER['REMOTE_ADDR'];
$success = false;
$error = '';
$settings = get_app_settings();
if (empty($settings['email'])) {
    $settings['email'] = 'contact@pava-shipping.fr';
}
if (empty($settings['telephone'])) {
    $settings['telephone'] = '0032465297645';
}
if (empty($settings['adresse'])) {
    $settings['adresse'] = 'Fluitbergstraat 59, 2900 Schoten';
}

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

$page_title = 'Contact — Renseignements et assistance logistique';
$page_description = "Contactez nos équipes logistiques pour toute question relative au suivi de vos colis ou à nos prestations d'acheminement.";
require_once __DIR__ . '/templates/public_header.php';

$banner_config = [
    'badge' => 'À votre écoute',
    'title' => 'Contactez notre équipe logistique',
    'description' => 'Notre équipe est à votre disposition pour répondre à vos demandes relatives au suivi de vos expéditions et à nos services.',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => '/'],
        ['label' => 'Contact']
    ],
    'actions' => [
        ['label' => 'Suivre un colis', 'url' => '/track', 'primary' => true],
    ],
    'image' => '/assets/img/site/contact-support.webp',
    'image_alt' => 'Opératrice du service client logistique et suivi des expéditions'
];
?>

<main class="flex-grow flex flex-col bg-white">
    <?php require __DIR__ . '/templates/page_banner.php'; ?>

    <section class="py-16 sm:py-20 bg-[#F4F3ED]" aria-labelledby="contact-section-title">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="contact-section-title" class="sr-only">Formulaire et coordonnées de contact</h2>
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row gap-8 lg:gap-12 items-start">
                
                <?php if (!empty($settings['email']) || !empty($settings['telephone']) || !empty($settings['adresse'])): ?>
                <div class="w-full md:w-5/12 flex flex-col gap-6">
                    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-stone-200 shadow-sm flex flex-col gap-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#6B6B6B]">Coordonnées</span>
                        
                        <?php if (!empty($settings['email'])): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-[#171717] rounded-lg flex items-center justify-center text-[#FFE500] shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-[#6B6B6B]">Email</span>
                                <a href="mailto:<?= escape_html($settings['email']) ?>" class="text-[#171717] font-semibold text-sm hover:underline"><?= escape_html($settings['email']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['telephone'])): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-[#171717] rounded-lg flex items-center justify-center text-[#FFE500] shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-[#6B6B6B]">Téléphone</span>
                                <a href="tel:<?= escape_html($settings['telephone']) ?>" class="text-[#171717] font-semibold text-sm hover:underline"><?= escape_html($settings['telephone']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['adresse'])): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-[#171717] rounded-lg flex items-center justify-center text-[#FFE500] shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-[#6B6B6B]">Adresse</span>
                                <span class="text-[#171717] font-semibold text-sm leading-relaxed"><?= nl2br(escape_html($settings['adresse'])) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-full md:w-7/12">
                <?php else: ?>
                <div class="max-w-xl mx-auto w-full">
                <?php endif; ?>
                
                    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-stone-200 shadow-sm">
                <?php if ($success): ?>
                    <div class="bg-status-delivered-bg text-status-delivered-text p-4 rounded-lg border border-status-delivered-bg mb-6 text-sm font-medium">
                        Votre message a bien été envoyé. Nous vous contacterons rapidement.
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="bg-danger/10 text-danger p-4 rounded-lg border border-danger/20 mb-6 text-sm font-medium">
                            <?= escape_html($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/contact" method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= escape_html(generate_csrf_token()) ?>">
                        
                        <!-- Honeypot -->
                        <div style="display:none;" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nom complet</label>
                            <input type="text" name="name" class="w-full px-4 h-12 rounded-lg border-[1.5px] border-slate-200 focus:border-primary focus:ring-0 outline-none transition-colors" required value="<?= escape_html($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Adresse e-mail</label>
                            <input type="email" name="email" class="w-full px-4 h-12 rounded-lg border-[1.5px] border-slate-200 focus:border-primary focus:ring-0 outline-none transition-colors" required value="<?= escape_html($_POST['email'] ?? '') ?>">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Message</label>
                            <textarea name="message" rows="5" class="w-full px-4 py-3 rounded-lg border-[1.5px] border-slate-200 focus:border-primary focus:ring-0 outline-none resize-none transition-colors" required><?= escape_html($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="w-full h-12 bg-[#171717] text-white hover:bg-black font-bold text-base rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 focus-brand-dark">
                            <span>Envoyer le message</span>
                            <svg class="w-4 h-4 text-[#FFE500]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/templates/public_footer.php'; ?>
