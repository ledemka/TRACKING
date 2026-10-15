<?php
// scripts/test-render-pages.php
// Script de test automatisé simulant le rendu des pages principales dans les 4 langues

require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../api/lib/status.php';
require_once __DIR__ . '/../templates/emails/tracking_email.php';

$languages = ['fr', 'en', 'de', 'nl'];

$expectations = [
    'fr' => [
        'nav_track' => 'Suivre un colis',
        'footer_rights' => 'Tous droits réservés',
        'status_shipped' => 'Expédié'
    ],
    'en' => [
        'nav_track' => 'Track a shipment',
        'footer_rights' => 'All rights reserved',
        'status_shipped' => 'Shipped'
    ],
    'de' => [
        'nav_track' => 'Sendung verfolgen',
        'footer_rights' => 'Alle Rechte vorbehalten',
        'status_shipped' => 'Versandt'
    ],
    'nl' => [
        'nav_track' => 'Pakket volgen',
        'footer_rights' => 'Alle rechten voorbehouden',
        'status_shipped' => 'Verzonden'
    ]
];

echo "========================================================\n";
echo "   TEST DE RENDU DES PAGES EN 4 LANGUES (FR, EN, DE, NL)\n";
echo "========================================================\n\n";

$allPassed = true;

foreach ($languages as $lang) {
    set_language($lang);
    echo "--- Test de la langue : [$lang] ---\n";

    // 1. Test Dictionnaire & Helper
    $navTrack = __('nav.track');
    $expected = $expectations[$lang]['nav_track'];
    if ($navTrack !== $expected) {
        echo "❌ Erreur helper __(): Attendu '$expected', obtenu '$navTrack'\n";
        $allPassed = false;
    } else {
        echo "✓ Clé nav.track : '$navTrack'\n";
    }

    // 2. Test formatage date localisée
    $testDate = '2026-10-14 15:30:00';
    $formatted = format_date($testDate);
    echo "✓ Date localisée ($testDate) : $formatted\n";

    // 3. Test statut & mode de transport
    $statusBadge = render_status_badge('shipped');
    $expectedStatus = $expectations[$lang]['status_shipped'];
    if (strpos($statusBadge, $expectedStatus) === false) {
        echo "❌ Erreur badge statut: '$expectedStatus' non trouvé dans le badge\n";
        $allPassed = false;
    } else {
        echo "✓ Badge statut : contient '$expectedStatus'\n";
    }

    $transportLabel = get_transport_mode_label('air');
    echo "✓ Mode de transport 'air' : '$transportLabel'\n";

    // 4. Test Rendu E-mail Transactionnel
    $mockShipment = [
        'tracking_number' => 'TEST-1234-XYZ',
        'recipient_name' => 'Jean Dupont',
        'city' => 'Berlin',
        'origin_city' => 'Paris',
        'transport_mode' => 'air',
        'carrier_name' => 'Air Cargo'
    ];
    $mockSettings = ['nom' => 'Pava Shipping', 'email' => 'contact@test.com'];
    $emailData = render_tracking_email($mockShipment, $mockSettings, 'https://test.com');
    if (empty($emailData['subject']) || empty($emailData['html']) || empty($emailData['text'])) {
        echo "❌ Erreur de génération d'email en [$lang]\n";
        $allPassed = false;
    } else {
        echo "✓ E-mail transactionnel généré (Sujet: '{$emailData['subject']}')\n";
    }

    // 5. Test Rendu Drapeau SVG et Sélecteur de Langue
    $flagSvg = render_flag_svg($lang);
    if (strpos($flagSvg, '<svg') === false || strpos($flagSvg, '</svg>') === false) {
        echo "❌ Erreur drapeau SVG pour [$lang]\n";
        $allPassed = false;
    } else {
        echo "✓ Drapeau SVG valide pour [$lang]\n";
    }

    $switcherHtml = render_language_switcher('dark');
    if (strpos($switcherHtml, 'expand_more') !== false) {
        echo "❌ Présence anormale du texte 'expand_more' dans le sélecteur pour [$lang]\n";
        $allPassed = false;
    } elseif (strpos($switcherHtml, 'lang-switcher-arrow') === false) {
        echo "❌ Flèche SVG animée absente du sélecteur pour [$lang]\n";
        $allPassed = false;
    } else {
        echo "✓ Sélecteur de langue propre (SVG drapeaux, flèche animée, sans texte d'icône brut)\n";
    }

    echo "\n";
}

if ($allPassed) {
    echo "========================================================\n";
    echo "RÉSULTAT : Tous les tests de rendu multilingue ont RÉUSSI !\n";
    echo "========================================================\n";
    exit(0);
} else {
    echo "========================================================\n";
    echo "RÉSULTAT : Des erreurs ont été détectées !\n";
    echo "========================================================\n";
    exit(1);
}
