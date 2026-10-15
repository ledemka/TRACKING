<?php
// api/lib/utils.php

function format_date($datetime_str, $format = 'd/m/Y à H:i') {
    if (!$datetime_str) return '';
    if (function_exists('format_localized_date')) {
        return format_localized_date($datetime_str, $format);
    }
    
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
            'nom' => 'Pava Shipping',
            'email' => 'contact@pava-shipping.fr',
            'telephone' => '0032465297645',
            'adresse' => 'Fluitbergstraat 59, 2900 Schoten',
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

if (!function_exists('haversine_km')) {
    /**
     * Distance à vol d'oiseau (km) entre deux points géographiques, formule de haversine.
     */
    function haversine_km(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(min(1.0, sqrt($a)));
    }
}

if (!function_exists('get_base_url')) {
    /**
     * Retourne l'URL de base canonique de l'application.
     * En production, s'appuie prioritairement sur APP_URL (ex: https://pava-shipping.fr)
     * pour garantir des balises SEO (canonical, hreflang, OpenGraph) irréprochables
     * sans jamais risquer d'exposer une adresse locale ou temporaire (127.0.0.1).
     * En local, s'adapte à l'hôte HTTP courant ($_SERVER['HTTP_HOST']).
     */
    function get_base_url(): string {
        $envUrl = getenv('APP_URL');
        $appEnv = getenv('APP_ENV') ?: 'local';

        // 1. En environnement de production, APP_URL est toujours prioritaire
        if ($appEnv === 'production' && !empty($envUrl)) {
            return rtrim($envUrl, '/');
        }

        // 2. Détection dynamique à partir de la requête courante
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $hostDomain = explode(':', $host)[0];
            $isLocalHost = in_array(strtolower($hostDomain), ['localhost', '127.0.0.1', '::1'], true);

            // Si l'hôte n'est pas localhost (ex: pava-shipping.fr sur SiteGround), utiliser APP_URL si disponible
            if (!$isLocalHost && !empty($envUrl)) {
                return rtrim($envUrl, '/');
            }

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $scheme = $isHttps ? 'https' : 'http';
            return $scheme . '://' . $host;
        }

        // 3. Fallback sur APP_URL ou domaine officiel
        return !empty($envUrl) ? rtrim($envUrl, '/') : 'https://pava-shipping.fr';
    }
}


