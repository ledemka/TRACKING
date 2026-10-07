<?php
// api/lib/utils.php

function format_date($datetime_str, $format = 'd/m/Y à H:i') {
    if (!$datetime_str) return '';
    
    // Le serveur de BDD et PHP stockent/traitent en UTC.
    $dt = new DateTime($datetime_str, new DateTimeZone('UTC'));
    
    // On convertit pour l'affichage selon le fuseau de l'app.
    $tz = getenv('APP_TIMEZONE') ?: 'Europe/Paris';
    try {
        $dt->setTimezone(new DateTimeZone($tz));
    } catch (\Exception $e) {
        $dt->setTimezone(new DateTimeZone('Europe/Paris'));
    }
    
    return $dt->format($format);
}

function get_app_settings() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM app_settings LIMIT 1");
    $settings = $stmt->fetch();
    if (!$settings) {
        return [
            'nom' => '[NOM DE MON ENTREPRISE]',
            'email' => '',
            'telephone' => '',
            'adresse' => '',
            'color_primary' => '#0B1F3A',
            'color_accent' => '#F59E0B',
            'logo' => ''
        ];
    }
    return $settings;
}

function mask_name($fullname) {
    $parts = explode(' ', trim($fullname));
    if (count($parts) > 1) {
        $last = array_pop($parts);
        $first = implode(' ', $parts);
        return $first . ' ' . mb_substr($last, 0, 1) . '.';
    }
    return mb_substr($fullname, 0, 1) . '.';
}
