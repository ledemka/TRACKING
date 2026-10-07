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
    
    // Si on veut des mois en français
    $format = str_replace('F', 'µµµ', $format);
    $format = str_replace('M', '§§§', $format);
    
    $res = $dt->format($format);
    
    $mois_long_fr = [
        'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril',
        'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août',
        'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'
    ];
    $mois_court_fr = [
        'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
        'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
        'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.'
    ];
    
    $res = str_replace('µµµ', $mois_long_fr[$dt->format('F')], $res);
    $res = str_replace('§§§', $mois_court_fr[$dt->format('M')], $res);
    
    return $res;
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
        preg_match('/^./us', $last, $matches_last);
        return $first . ' ' . ($matches_last[0] ?? '') . '.';
    }
    preg_match('/^./us', $fullname, $matches_full);
    return ($matches_full[0] ?? '') . '.';
}
