<?php
// api/lib/i18n.php
// Système centralisé d'internationalisation (i18n) pour l'application TRACKING

const SUPPORTED_LANGUAGES = ['fr', 'en', 'de', 'nl'];
const DEFAULT_LANGUAGE = 'fr';

/**
 * Détecte la langue préférée du navigateur via l'en-tête Accept-Language en respectant les pondérations (q-factor).
 * Retourne le code langue supporté ('fr', 'en', 'de', 'nl') ou null si aucune correspondance.
 */
function detect_browser_language(): ?string {
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return null;
    }

    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $parts = explode(',', $header);
    $candidates = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;

        $q = 1.0;
        if (str_contains($part, ';')) {
            $subParts = explode(';', $part);
            $langToken = trim($subParts[0]);
            foreach (array_slice($subParts, 1) as $param) {
                $param = trim($param);
                if (stripos($param, 'q=') === 0) {
                    $qVal = (float) substr($param, 2);
                    if ($qVal >= 0 && $qVal <= 1.0) {
                        $q = $qVal;
                    }
                }
            }
        } else {
            $langToken = $part;
        }

        // Récupérer le code langue principal (ex: 'fr-FR' -> 'fr', 'en-US' -> 'en')
        $mainCode = strtolower(substr($langToken, 0, 2));
        if (in_array($mainCode, SUPPORTED_LANGUAGES, true)) {
            // Conserver le meilleur score q pour chaque code
            if (!isset($candidates[$mainCode]) || $q > $candidates[$mainCode]) {
                $candidates[$mainCode] = $q;
            }
        }
    }

    if (empty($candidates)) {
        return null;
    }

    // Trier par qualité décroissante
    arsort($candidates);
    return array_key_first($candidates);
}

/**
 * Détecte le pays via géolocalisation IP comme indice secondaire (sans GPS, respectueux de la vie privée).
 * Retourne une langue supportée compatible ('fr', 'en', 'de', 'nl') ou null en cas d'échec ou d'absence de correspondance.
 */
function detect_ip_geolocation_language(): ?string {
    // Si déjà résolu en session, éviter toute opération répétée
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['geo_lang_detected'])) {
        return $_SESSION['geo_lang_detected'] ?: null;
    }

    $countryCode = null;

    // 1. En-têtes serveur standards (SiteGround, Cloudflare, proxys, etc.)
    $headerCandidates = [
        'HTTP_CF_IPCOUNTRY',
        'HTTP_GEOIP_COUNTRY_CODE',
        'GEOIP_COUNTRY_CODE',
        'HTTP_X_COUNTRY_CODE',
        'HTTP_X_GEOIP_COUNTRY', // Pratique pour tests unitaires/automatisés
    ];

    foreach ($headerCandidates as $header) {
        if (!empty($_SERVER[$header])) {
            $val = strtoupper(trim($_SERVER[$header]));
            if (strlen($val) === 2 && ctype_alpha($val) && $val !== 'XX' && $val !== 'T1') {
                $countryCode = $val;
                break;
            }
        }
    }

    // 2. Si aucun en-tête n'est présent et qu'une IP publique valide existe
    if (!$countryCode && !empty($_SERVER['REMOTE_ADDR'])) {
        $ip = trim($_SERVER['REMOTE_ADDR']);
        // Ignorer les adresses locales, loopback ou privées pour préserver la réactivité
        $isPrivate = !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if (!$isPrivate) {
            // Appel léger sécurisé avec timeout strict de 500ms
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 0.5,
                    'ignore_errors' => true,
                    'user_agent' => 'PavaTracking/1.0',
                ]
            ]);
            $res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,countryCode", false, $ctx);
            if ($res !== false) {
                $data = @json_decode($res, true);
                if (!empty($data) && ($data['status'] ?? '') === 'success' && !empty($data['countryCode'])) {
                    $countryCode = strtoupper(trim($data['countryCode']));
                }
            }
        }
    }

    // 3. Mapping du pays vers une langue supportée (Indice secondaire)
    $lang = null;
    if ($countryCode) {
        $countryLangMap = [
            'FR' => 'fr',
            'BE' => 'fr',
            'MC' => 'fr',
            'SN' => 'fr',
            'CI' => 'fr',
            'DE' => 'de',
            'AT' => 'de',
            'CH' => 'de',
            'NL' => 'nl',
            'GB' => 'en',
            'US' => 'en',
            'CA' => 'en',
            'IE' => 'en',
            'AU' => 'en',
            'NZ' => 'en',
            'LU' => 'fr',
        ];
        $lang = $countryLangMap[$countryCode] ?? null;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['geo_lang_detected'] = $lang ?: false;
    }

    return $lang;
}

/**
 * Initialise et détermine la langue active de la requête selon les 5 règles de priorité :
 * 1. Préférence explicite enregistrée pour l'utilisateur connecté.
 * 2. Choix transmis dans l'URL ou effectué manuellement (cookie/session).
 * 3. Langue préférée du navigateur (Accept-Language).
 * 4. Pays détecté par géolocalisation IP comme indice secondaire (fallback si échec).
 * 5. Langue par défaut : français.
 */
function init_i18n(): string {
    global $current_lang, $pdo;

    if (!empty($current_lang)) {
        return $current_lang;
    }

    $lang = null;
    $isManualSwitch = false;

    // Détecter un choix transmis explicitement dans l'URL (?lang=xx ou préfixe de route)
    $requestedLang = null;
    if (!empty($_GET['lang'])) {
        $candidate = strtolower(trim($_GET['lang']));
        if (in_array($candidate, SUPPORTED_LANGUAGES, true)) {
            $requestedLang = $candidate;
        }
    }

    // Si une demande explicite est transmise dans l'URL courante, c'est un choix manuel prioritaire
    if ($requestedLang !== null) {
        $lang = $requestedLang;
        $isManualSwitch = true;
    }

    $isLoggedIn = (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id']));
    $userId = $isLoggedIn ? (int)$_SESSION['user_id'] : null;

    // RÈGLE 1 : Respecter une préférence explicite déjà enregistrée pour l'utilisateur connecté (si pas de switch manuel dans l'URL)
    if (!$lang && $isLoggedIn) {
        // Vérifier d'abord si déjà en session
        if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGUAGES, true) && !empty($_SESSION['lang_source_user'])) {
            $lang = $_SESSION['lang'];
        } else {
            // Charger depuis la base de données
            try {
                if (isset($pdo)) {
                    $stmt = $pdo->prepare("SELECT lang FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $userDbLang = $stmt->fetchColumn();
                    if ($userDbLang && in_array(strtolower($userDbLang), SUPPORTED_LANGUAGES, true)) {
                        $lang = strtolower($userDbLang);
                        $_SESSION['lang_source_user'] = true;
                    }
                }
            } catch (\Throwable $e) {
                // Secours silencieux
            }
        }
    }

    // RÈGLE 2 : Pour une première visite ou choix manuel préalable (session / cookie)
    if (!$lang && session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGUAGES, true)) {
        $lang = $_SESSION['lang'];
    }

    if (!$lang && !empty($_COOKIE['app_lang']) && in_array(strtolower($_COOKIE['app_lang']), SUPPORTED_LANGUAGES, true)) {
        $lang = strtolower($_COOKIE['app_lang']);
    }

    // RÈGLE 3 : Si aucune préférence n'existe, détecter la langue préférée du navigateur à partir de Accept-Language
    if (!$lang) {
        $browserLang = detect_browser_language();
        if ($browserLang) {
            $lang = $browserLang;
        }
    }

    // RÈGLE 4 : Indice secondaire par géolocalisation IP (si aucune langue compatible identifiée par Accept-Language)
    if (!$lang) {
        $ipGeoLang = detect_ip_geolocation_language();
        if ($ipGeoLang) {
            $lang = $ipGeoLang;
        }
    }

    // RÈGLE 5 : Si aucune langue compatible n'est identifiée, utiliser le français
    if (!$lang || !in_array($lang, SUPPORTED_LANGUAGES, true)) {
        $lang = DEFAULT_LANGUAGE;
    }

    $current_lang = $lang;

    // Persistance session
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['lang'] = $lang;
        if ($isManualSwitch) {
            $_SESSION['lang_manual'] = true;
        }
    }

    // Si l'utilisateur connecté a effectué un changement manuel, enregistrer dans son profil en BDD
    if ($isManualSwitch && $isLoggedIn) {
        try {
            if (isset($pdo)) {
                $stmt = $pdo->prepare("UPDATE users SET lang = ? WHERE id = ?");
                $stmt->execute([$lang, $userId]);
                $_SESSION['lang_source_user'] = true;
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement
        }
    }

    // Persistance cookie (visiteurs anonymes et inter-sessions)
    if (!headers_sent()) {
        $cookieLang = $_COOKIE['app_lang'] ?? '';
        if ($cookieLang !== $lang) {
            setcookie('app_lang', $lang, time() + 365 * 86400, '/', '', false, false);
        }
        if ($isManualSwitch) {
            setcookie('app_lang_manual', '1', time() + 365 * 86400, '/', '', false, false);
        }
    }

    return $current_lang;
}

/**
 * Retourne la langue active courante ('fr', 'en', 'de', 'nl').
 */
function get_current_lang(): string {
    global $current_lang;
    if (empty($current_lang)) {
        return init_i18n();
    }
    return $current_lang;
}

/**
 * Modifie manuellement la langue courante et la persiste en profil / session / cookie.
 */
function set_current_lang(string $lang): void {
    global $current_lang, $pdo;
    $lang = strtolower(trim($lang));
    if (in_array($lang, SUPPORTED_LANGUAGES, true)) {
        $current_lang = $lang;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['lang'] = $current_lang;
            $_SESSION['lang_manual'] = true;
            if (!empty($_SESSION['user_id'])) {
                try {
                    if (isset($pdo)) {
                        $stmt = $pdo->prepare("UPDATE users SET lang = ? WHERE id = ?");
                        $stmt->execute([$current_lang, $_SESSION['user_id']]);
                        $_SESSION['lang_source_user'] = true;
                    }
                } catch (\Throwable $e) {
                }
            }
        }
        if (!headers_sent()) {
            setcookie('app_lang', $current_lang, time() + 365 * 86400, '/', '', false, false);
            setcookie('app_lang_manual', '1', time() + 365 * 86400, '/', '', false, false);
        }
    }
}

/**
 * Alias pour set_current_lang.
 */
function set_language(string $lang): void {
    set_current_lang($lang);
}

/**
 * Alias pour get_current_lang.
 */
function get_current_language(): string {
    return get_current_lang();
}

/**
 * Métadonnées des langues disponibles.
 */
function get_available_languages(): array {
    return [
        'fr' => ['code' => 'fr', 'label' => 'Français',    'native' => 'Français',   'flag' => 'fr', 'locale' => 'fr_FR'],
        'en' => ['code' => 'en', 'label' => 'English',     'native' => 'English',    'flag' => 'gb', 'locale' => 'en_GB'],
        'de' => ['code' => 'de', 'label' => 'Deutsch',     'native' => 'Deutsch',    'flag' => 'de', 'locale' => 'de_DE'],
        'nl' => ['code' => 'nl', 'label' => 'Nederlands',  'native' => 'Nederlands', 'flag' => 'nl', 'locale' => 'nl_NL'],
    ];
}

/**
 * Liste des pays supportés pour les adresses et téléphones internationaux.
 * Note : Distingue clairement les pays (France, Belgique, etc.) des langues (fr, en, de, nl).
 */
function get_supported_countries(): array {
    return [
        'FR' => ['code' => 'FR', 'name' => 'France',        'dial' => '+33',  'flag' => 'fr'],
        'BE' => ['code' => 'BE', 'name' => 'Belgique',      'dial' => '+32',  'flag' => 'be'],
        'DE' => ['code' => 'DE', 'name' => 'Allemagne',     'dial' => '+49',  'flag' => 'de'],
        'NL' => ['code' => 'NL', 'name' => 'Pays-Bas',      'dial' => '+31',  'flag' => 'nl'],
        'GB' => ['code' => 'GB', 'name' => 'Royaume-Uni',   'dial' => '+44',  'flag' => 'gb'],
        'CH' => ['code' => 'CH', 'name' => 'Suisse',        'dial' => '+41',  'flag' => 'ch'],
        'LU' => ['code' => 'LU', 'name' => 'Luxembourg',    'dial' => '+352', 'flag' => 'lu'],
        'IT' => ['code' => 'IT', 'name' => 'Italie',        'dial' => '+39',  'flag' => 'it'],
    ];
}

/**
 * Génère le SVG d'un drapeau avec dimensions homogènes et rendu vectoriel net.
 * Évite les emojis dont le rendu est imprévisible selon les OS (ex: lettres grises sur Windows).
 *
 * @param string $countryOrCode Code pays ('fr', 'gb', 'be', etc.) ou code langue ('en' -> 'gb')
 * @param string $extraClass Classes CSS (ex: 'w-5 h-3.5')
 * @return string Balise <svg> complète
 */
function render_flag_svg(string $countryOrCode, string $extraClass = 'w-5 h-3.5'): string {
    $code = strtolower(trim($countryOrCode));
    // L'anglais n'est pas un pays : dans le sélecteur de langue, associer à l'Union Jack britannique
    if ($code === 'en') {
        $code = 'gb';
    }

    $classAttr = 'class="inline-block shrink-0 rounded-[2px] shadow-sm ' . htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8') . '"';

    switch ($code) {
        case 'fr':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="10" height="20" fill="#002654"/><rect x="10" width="10" height="20" fill="#FFFFFF"/><rect x="20" width="10" height="20" fill="#CE1126"/></svg>';

        case 'gb':
            static $gbCounter = 0;
            $gbCounter++;
            $clipId = 'gb-flag-' . $gbCounter;
            return '<svg ' . $classAttr . ' viewBox="0 0 60 30" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><clipPath id="' . $clipId . '"><path d="M30 15h30v15zM30 15V0H0zM30 15H0v15zM30 15V0h30z"/></clipPath><path fill="#012169" d="M0 0h60v30H0z"/><path stroke="#fff" stroke-width="6" d="M0 0l60 30m0-30L0 30"/><path stroke="#C8102E" stroke-width="4" clip-path="url(#' . $clipId . ')" d="M0 0l60 30m0-30L0 30"/><path stroke="#fff" stroke-width="10" d="M30 0v30M0 15h60"/><path stroke="#C8102E" stroke-width="6" d="M30 0v30M0 15h60"/></svg>';

        case 'de':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="30" height="6.67" fill="#000000"/><rect y="6.67" width="30" height="6.67" fill="#DD0000"/><rect y="13.33" width="30" height="6.67" fill="#FFCE00"/></svg>';

        case 'nl':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="30" height="6.67" fill="#AE1C28"/><rect y="6.67" width="30" height="6.67" fill="#FFFFFF"/><rect y="13.33" width="30" height="6.67" fill="#21468B"/></svg>';

        case 'be':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="10" height="20" fill="#000000"/><rect x="10" width="10" height="20" fill="#FDDA24"/><rect x="20" width="10" height="20" fill="#EF3340"/></svg>';

        case 'ch':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="30" height="20" fill="#D52B1E"/><rect x="13" y="4" width="4" height="12" fill="#FFFFFF"/><rect x="9" y="8" width="12" height="4" fill="#FFFFFF"/></svg>';

        case 'lu':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="30" height="6.67" fill="#EA141D"/><rect y="6.67" width="30" height="6.67" fill="#FFFFFF"/><rect y="13.33" width="30" height="6.67" fill="#00A1DE"/></svg>';

        case 'it':
            return '<svg ' . $classAttr . ' viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:3/2;"><rect width="10" height="20" fill="#009246"/><rect x="10" width="10" height="20" fill="#FFFFFF"/><rect x="20" width="10" height="20" fill="#CE2B37"/></svg>';

        default:
            return '<span class="inline-block px-1 bg-slate-200 text-slate-700 text-[10px] font-bold rounded leading-none">' . htmlspecialchars(strtoupper(substr($code, 0, 2)), ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

/**
 * Charge un dictionnaire de langue (avec cache mémoire par requête).
 */
function load_language_dictionary(string $lang): array {
    global $translations_cache;
    if (!isset($translations_cache)) {
        $translations_cache = [];
    }
    if (isset($translations_cache[$lang])) {
        return $translations_cache[$lang];
    }

    $file = __DIR__ . '/../../lang/' . $lang . '.php';
    if (file_exists($file)) {
        $dict = require $file;
        $translations_cache[$lang] = is_array($dict) ? $dict : [];
    } else {
        $translations_cache[$lang] = [];
    }
    return $translations_cache[$lang];
}

/**
 * Fonction centrale de traduction avec interpolation et repli sécurisé sur le français.
 *
 * @param string $key Clé de traduction (ex: 'status.shipped', 'common.save')
 * @param array $params Tableau associatif de paramètres de remplacement (ex: ['reference' => '123'])
 * @param string|null $lang Langue cible explicite (si null, utilise la langue active courante)
 * @return string
 */
function __(string $key, array $params = [], ?string $lang = null): string {
    $lang = $lang !== null ? strtolower(trim($lang)) : get_current_lang();
    if (!in_array($lang, SUPPORTED_LANGUAGES, true)) {
        $lang = DEFAULT_LANGUAGE;
    }
    $dict = load_language_dictionary($lang);

    $translation = null;
    if (isset($dict[$key])) {
        $translation = $dict[$key];
    } elseif ($lang !== DEFAULT_LANGUAGE) {
        // Repli sur le français
        $fallbackDict = load_language_dictionary(DEFAULT_LANGUAGE);
        if (isset($fallbackDict[$key])) {
            $translation = $fallbackDict[$key];
        }
    }

    // Si la clé est introuvable même en français, retourner la clé elle-même
    if ($translation === null) {
        $translation = $key;
    }

    // Interpolation des paramètres (:param ou {param})
    if (!empty($params)) {
        $replace = [];
        foreach ($params as $paramKey => $paramVal) {
            $replace[':' . $paramKey] = (string)$paramVal;
            $replace['{' . $paramKey . '}'] = (string)$paramVal;
        }
        $translation = strtr($translation, $replace);
    }

    return $translation;
}

/**
 * Échappe et affiche directement le texte traduit en contexte HTML.
 */
function _e(string $key, array $params = []): void {
    echo htmlspecialchars(__($key, $params), ENT_QUOTES, 'UTF-8');
}

/**
 * Traduit et prépare une chaîne pour un attribut HTML (value, placeholder, title, etc.).
 */
function __attr(string $key, array $params = []): string {
    return htmlspecialchars(__($key, $params), ENT_QUOTES, 'UTF-8');
}

/**
 * Traduit et encode pour une utilisation sécurisée dans un script JavaScript.
 */
function __js(string $key, array $params = []): string {
    return json_encode(__($key, $params), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Construit l'URL localisée correspondant à la page actuelle pour une langue cible.
 * Conserve rigoureusement la page consultée et tous ses paramètres existants.
 */
function get_localized_url(string $lang, bool $absolute = false): string {
    $lang = strtolower(trim($lang));
    if (!in_array($lang, SUPPORTED_LANGUAGES, true)) {
        $lang = DEFAULT_LANGUAGE;
    }

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $parsed = parse_url($requestUri);
    $path = $parsed['path'] ?? '/';

    // Nettoyer tout préfixe de langue existant (/fr, /en, /de, /nl)
    $cleanPath = preg_replace('#^/(fr|en|de|nl)(/|$)#i', '/', $path);
    if ($cleanPath === '' || $cleanPath === false) {
        $cleanPath = '/';
    }

    // Normaliser /index.php en /
    if ($cleanPath === '/index.php') {
        $cleanPath = '/';
    }

    // Construire le chemin préfixé
    $localizedPath = ($cleanPath === '/') ? '/' . $lang : '/' . $lang . $cleanPath;

    // Conserver tous les paramètres GET existants à l'exception de 'lang'
    $queryParams = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $queryParams);
    }
    unset($queryParams['lang']);

    $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
    $finalUri = $localizedPath . $queryString;

    if ($absolute) {
        $baseUrl = function_exists('get_base_url') ? get_base_url() : 'https://pava-shipping.fr';
        return $baseUrl . $finalUri;
    }

    return $finalUri;
}

/**
 * Retourne l'URL canonique non-préfixée pour la page courante (compatible x-default).
 */
function get_canonical_url(bool $absolute = true): string {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $parsed = parse_url($requestUri);
    $path = $parsed['path'] ?? '/';

    $cleanPath = preg_replace('#^/(fr|en|de|nl)(/|$)#i', '/', $path);
    if ($cleanPath === '' || $cleanPath === false) {
        $cleanPath = '/';
    }
    if ($cleanPath === '/index.php') {
        $cleanPath = '/';
    }

    $queryParams = [];
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $queryParams);
    }
    unset($queryParams['lang']);
    $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
    $finalUri = $cleanPath . $queryString;

    if ($absolute) {
        $baseUrl = function_exists('get_base_url') ? get_base_url() : 'https://pava-shipping.fr';
        return $baseUrl . $finalUri;
    }
    return $finalUri;
}

/**
 * Construit l'URL de bascule vers une langue donnée en conservant tous les paramètres GET existants.
 */
function get_language_switch_url(string $lang): string {
    return htmlspecialchars(get_localized_url($lang, false), ENT_QUOTES, 'UTF-8');
}

/**
 * Génère le composant visuel du sélecteur de langue (HTML accessible, responsive, codes et noms clairs).
 *
 * @param string $theme 'dark' (header admin ou navbar sombre) ou 'light' (footer ou page claire)
 * @return string HTML du sélecteur
 */
function render_language_switcher(string $theme = 'dark'): string {
    $current = get_current_lang();
    $languages = get_available_languages();
    $currentMeta = $languages[$current] ?? $languages[DEFAULT_LANGUAGE];
    $currentFlag = $currentMeta['flag'] ?? $current;

    $isDark = ($theme === 'dark');
    $btnBg = $isDark ? 'bg-white/10 hover:bg-white/15 text-white border-white/15' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 border-slate-300';
    $menuBg = $isDark ? 'bg-[#1e1e1e] border-white/15 text-white shadow-2xl' : 'bg-white border-slate-200 text-slate-800 shadow-xl';
    $itemHover = $isDark ? 'hover:bg-white/10' : 'hover:bg-slate-50';
    $activeColor = $isDark ? 'text-[#FFE500] font-bold bg-white/5' : 'text-blue-600 font-bold bg-slate-50';

    $uniqueId = 'lang-switcher-' . substr(md5(uniqid((string)mt_rand(), true)), 0, 6);

    ob_start();
    ?>
    <div class="relative inline-block text-left" id="<?= $uniqueId ?>" data-lang-switcher>
        <button type="button" 
                class="lang-switcher-trigger inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer select-none focus:outline-none focus:ring-2 focus:ring-[#FFE500] <?= $btnBg ?>"
                aria-haspopup="true" 
                aria-expanded="false" 
                aria-label="<?= escape_html($currentMeta['label']) ?>"
                title="<?= escape_html($currentMeta['label']) ?> (<?= strtoupper($current) ?>)"
                onclick="window.toggleLanguageMenu(this)">
            <?= render_flag_svg($currentFlag, 'w-5 h-3.5') ?>
            <span class="lang-code-badge font-bold uppercase text-[11px] tracking-wide"><?= strtoupper($current) ?></span>
            <svg class="lang-switcher-arrow w-3.5 h-3.5 transition-transform duration-200 shrink-0 opacity-80" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
        <div class="hidden absolute right-0 mt-1.5 w-48 rounded-xl border py-1.5 z-[1060] transition-all shadow-2xl <?= $menuBg ?>" 
             role="menu" 
             aria-orientation="vertical"
             style="z-index: 1060;">
            <?php foreach ($languages as $code => $meta): 
                $isActive = ($code === $current);
                $url = get_language_switch_url($code);
                $flagCode = $meta['flag'] ?? $code;
            ?>
                <a href="<?= $url ?>" 
                   class="flex items-center justify-between px-3.5 py-2.5 text-xs transition-colors <?= $itemHover ?> <?= $isActive ? $activeColor : '' ?>" 
                   role="menuitem"
                   lang="<?= $code ?>">
                    <span class="flex items-center gap-2.5">
                        <?= render_flag_svg($flagCode, 'w-5 h-3.5') ?>
                        <span class="font-medium text-xs leading-none"><?= escape_html($meta['native']) ?></span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] uppercase font-bold opacity-60"><?= strtoupper($code) ?></span>
                        <?php if ($isActive): ?>
                            <svg class="w-4 h-4 text-[#FFE500] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    if (typeof window.toggleLanguageMenu !== 'function') {
        window.toggleLanguageMenu = function(btn) {
            var root = btn.closest('[data-lang-switcher]');
            if (!root) return;
            var menu = root.querySelector('[role="menu"]');
            if (!menu) return;
            var isExp = btn.getAttribute('aria-expanded') === 'true';
            var arrow = btn.querySelector('.lang-switcher-arrow');

            // Fermer tous les autres sélecteurs de langue ouverts
            document.querySelectorAll('[data-lang-switcher]').forEach(function(other){
                if (other !== root) {
                    var otherMenu = other.querySelector('[role="menu"]');
                    var otherBtn = other.querySelector('button');
                    if (otherMenu) otherMenu.classList.add('hidden');
                    if (otherBtn) {
                        otherBtn.setAttribute('aria-expanded', 'false');
                        var otherArr = otherBtn.querySelector('.lang-switcher-arrow');
                        if (otherArr) otherArr.style.transform = '';
                    }
                }
            });

            if (isExp) {
                menu.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.style.transform = '';
            } else {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        };

        document.addEventListener('click', function(e){
            document.querySelectorAll('[data-lang-switcher]').forEach(function(root){
                if (!root.contains(e.target)) {
                    var menu = root.querySelector('[role="menu"]');
                    var btn = root.querySelector('button');
                    if (menu && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        if (btn) {
                            btn.setAttribute('aria-expanded', 'false');
                            var arrow = btn.querySelector('.lang-switcher-arrow');
                            if (arrow) arrow.style.transform = '';
                        }
                    }
                }
            });
        });

        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') {
                document.querySelectorAll('[data-lang-switcher]').forEach(function(root){
                    var menu = root.querySelector('[role="menu"]');
                    var btn = root.querySelector('button');
                    if (menu && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        if (btn) {
                            btn.setAttribute('aria-expanded', 'false');
                            var arrow = btn.querySelector('.lang-switcher-arrow');
                            if (arrow) arrow.style.transform = '';
                            btn.focus();
                        }
                    }
                });
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Génère un champ téléphone avec sélecteur d'indicatif international (Drapeau pays SVG + indicatif).
 * Distingue rigoureusement les pays associés aux indicatifs (+33 France, +44 Royaume-Uni, etc.) de la langue du site.
 */
function render_phone_input_field(string $name, string $value = '', bool $hasError = false, string $placeholder = '06 00 00 00 00', string $id = ''): string {
    $fieldId = $id ?: $name . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 5);
    $countries = get_supported_countries();

    // Détection de l'indicatif courant
    $selectedCountry = 'FR';
    $trimmedVal = trim($value);
    foreach ($countries as $cCode => $cMeta) {
        if (str_starts_with($trimmedVal, $cMeta['dial'])) {
            $selectedCountry = $cCode;
            break;
        }
    }

    $currentCountry = $countries[$selectedCountry] ?? $countries['FR'];
    $errorClass = $hasError ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300 focus:border-action focus:ring-1 focus:ring-action';

    ob_start();
    ?>
    <div class="flex items-center gap-2 max-w-md" data-phone-component id="comp-<?= $fieldId ?>">
        <div class="relative shrink-0" data-phone-prefix-dropdown>
            <button type="button" 
                    class="phone-prefix-btn h-10 px-2.5 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-semibold flex items-center gap-1.5 transition-all select-none focus:outline-none focus:ring-2 focus:ring-action cursor-pointer"
                    aria-haspopup="true"
                    aria-expanded="false"
                    title="<?= escape_html($currentCountry['name']) ?> (<?= $currentCountry['dial'] ?>)"
                    onclick="window.togglePhonePrefixMenu(this)">
                <span class="phone-flag flex items-center"><?= render_flag_svg($currentCountry['flag'], 'w-5 h-3.5') ?></span>
                <span class="phone-dial font-bold text-slate-700"><?= $currentCountry['dial'] ?></span>
                <svg class="phone-chevron w-3.5 h-3.5 transition-transform duration-200 shrink-0 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div class="hidden absolute left-0 mt-1.5 w-56 max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 z-[2100] shadow-xl" role="menu">
                <?php foreach ($countries as $cCode => $cMeta): ?>
                    <button type="button" 
                            class="w-full flex items-center justify-between px-3 py-2 text-xs text-left text-slate-700 hover:bg-slate-100 transition-colors border-0 bg-transparent cursor-pointer" 
                            role="menuitem"
                            onclick="window.selectPhoneCountry(this, '<?= $cMeta['dial'] ?>', '<?= $cMeta['flag'] ?>', '<?= escape_html($cMeta['name']) ?>')">
                        <span class="flex items-center gap-2">
                            <?= render_flag_svg($cMeta['flag'], 'w-5 h-3.5') ?>
                            <span class="font-medium text-slate-800"><?= escape_html($cMeta['name']) ?></span>
                        </span>
                        <span class="font-bold text-slate-500 font-mono"><?= $cMeta['dial'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="text" 
               name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" 
               id="<?= htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8') ?>" 
               value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" 
               placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>" 
               class="h-10 border px-3 rounded-lg flex-1 text-sm outline-none transition-colors <?= $errorClass ?>">
    </div>
    <script>
    if (typeof window.togglePhonePrefixMenu !== 'function') {
        window.togglePhonePrefixMenu = function(btn) {
            var root = btn.closest('[data-phone-prefix-dropdown]');
            if (!root) return;
            var menu = root.querySelector('[role="menu"]');
            var isExp = btn.getAttribute('aria-expanded') === 'true';
            var arrow = btn.querySelector('.phone-chevron');

            // Fermer les autres menus
            document.querySelectorAll('[data-phone-prefix-dropdown]').forEach(function(other){
                if (other !== root) {
                    var m = other.querySelector('[role="menu"]');
                    var b = other.querySelector('button');
                    if (m) m.classList.add('hidden');
                    if (b) {
                        b.setAttribute('aria-expanded', 'false');
                        var a = b.querySelector('.phone-chevron');
                        if (a) a.style.transform = '';
                    }
                }
            });

            if (isExp) {
                menu.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.style.transform = '';
            } else {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        };

        window.selectPhoneCountry = function(item, dial, flagCode, countryName) {
            var comp = item.closest('[data-phone-component]');
            if (!comp) return;
            var btn = comp.querySelector('[data-phone-prefix-dropdown] button');
            var dialSpan = btn.querySelector('.phone-dial');
            var flagSpan = btn.querySelector('.phone-flag');
            var input = comp.querySelector('input[type="text"]');
            var oldDial = dialSpan.textContent.trim();

            dialSpan.textContent = dial;
            btn.title = countryName + ' (' + dial + ')';

            // Dictionnaires SVG pour mise à jour instantanée
            var svgs = {
                'fr': '<?= addslashes(render_flag_svg('fr', 'w-5 h-3.5')) ?>',
                'be': '<?= addslashes(render_flag_svg('be', 'w-5 h-3.5')) ?>',
                'de': '<?= addslashes(render_flag_svg('de', 'w-5 h-3.5')) ?>',
                'nl': '<?= addslashes(render_flag_svg('nl', 'w-5 h-3.5')) ?>',
                'gb': '<?= addslashes(render_flag_svg('gb', 'w-5 h-3.5')) ?>',
                'ch': '<?= addslashes(render_flag_svg('ch', 'w-5 h-3.5')) ?>',
                'lu': '<?= addslashes(render_flag_svg('lu', 'w-5 h-3.5')) ?>',
                'it': '<?= addslashes(render_flag_svg('it', 'w-5 h-3.5')) ?>'
            };
            if (svgs[flagCode]) {
                flagSpan.innerHTML = svgs[flagCode];
            }

            // Mettre à jour l'input si l'ancien indicatif y était préfixé
            if (input) {
                var curVal = input.value.trim();
                if (curVal.indexOf(oldDial) === 0) {
                    input.value = dial + ' ' + curVal.substring(oldDial.length).trim();
                } else if (!curVal || curVal.indexOf('+') === 0) {
                    input.value = dial + ' ' + curVal.replace(/^\+\d+\s*/, '');
                }
                input.focus();
            }

            var menu = item.closest('[role="menu"]');
            if (menu) menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            var arrow = btn.querySelector('.phone-chevron');
            if (arrow) arrow.style.transform = '';
        };

        document.addEventListener('click', function(e){
            document.querySelectorAll('[data-phone-prefix-dropdown]').forEach(function(root){
                if (!root.contains(e.target)) {
                    var menu = root.querySelector('[role="menu"]');
                    var btn = root.querySelector('button');
                    if (menu && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        if (btn) {
                            btn.setAttribute('aria-expanded', 'false');
                            var arrow = btn.querySelector('.phone-chevron');
                            if (arrow) arrow.style.transform = '';
                        }
                    }
                }
            });
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Formatage de date localisé selon la langue active.
 */
function format_localized_date($datetime_str, $format = 'd/m/Y à H:i'): string {
    if (!$datetime_str) return '';

    $lang = get_current_lang();
    $dt = new DateTime($datetime_str, new DateTimeZone('UTC'));

    $tz = getenv('APP_TIMEZONE') ?: 'Europe/Paris';
    try {
        $dt->setTimezone(new DateTimeZone($tz));
    } catch (\Exception $e) {
        $dt->setTimezone(new DateTimeZone('Europe/Paris'));
    }

    // Traductions des mois longs selon la langue
    $monthsLong = [
        'fr' => [
            'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril',
            'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août',
            'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'
        ],
        'en' => [
            'January' => 'January', 'February' => 'February', 'March' => 'March', 'April' => 'April',
            'May' => 'May', 'June' => 'June', 'July' => 'July', 'August' => 'August',
            'September' => 'September', 'October' => 'October', 'November' => 'November', 'December' => 'December'
        ],
        'de' => [
            'January' => 'Januar', 'February' => 'Februar', 'March' => 'März', 'April' => 'April',
            'May' => 'Mai', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'August',
            'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Dezember'
        ],
        'nl' => [
            'January' => 'januari', 'February' => 'februari', 'March' => 'maart', 'April' => 'april',
            'May' => 'mei', 'June' => 'juni', 'July' => 'juli', 'August' => 'augustus',
            'September' => 'september', 'October' => 'oktober', 'November' => 'november', 'December' => 'december'
        ],
    ];

    // Traductions des mois courts selon la langue
    $monthsShort = [
        'fr' => [
            'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
            'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
            'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.'
        ],
        'en' => [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr',
            'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Aug',
            'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dec'
        ],
        'de' => [
            'Jan' => 'Jan.', 'Feb' => 'Feb.', 'Mar' => 'März', 'Apr' => 'Apr.',
            'May' => 'Mai', 'Jun' => 'Juni', 'Jul' => 'Juli', 'Aug' => 'Aug.',
            'Sep' => 'Sept.', 'Oct' => 'Okt.', 'Nov' => 'Nov.', 'Dec' => 'Dez.'
        ],
        'nl' => [
            'Jan' => 'jan.', 'Feb' => 'feb.', 'Mar' => 'mrt.', 'Apr' => 'apr.',
            'May' => 'mei', 'Jun' => 'juni', 'Jul' => 'juli', 'Aug' => 'aug.',
            'Sep' => 'sep.', 'Oct' => 'okt.', 'Nov' => 'nov.', 'Dec' => 'dec.'
        ],
    ];

    // Adapter le connecteur "à" / "at" / "um" / "om" si le format contient "à"
    if (strpos($format, ' à ') !== false) {
        if ($lang === 'en') {
            $format = str_replace(' à ', ' \a\t ', $format);
        } elseif ($lang === 'de') {
            $format = str_replace(' à ', ' \u\m ', $format);
        } elseif ($lang === 'nl') {
            $format = str_replace(' à ', ' \o\m ', $format);
        }
    }

    $format = str_replace('F', 'µµµ', $format);
    $format = str_replace('M', '§§§', $format);
    $res = $dt->format($format);

    $monthListLong = $monthsLong[$lang] ?? $monthsLong['fr'];
    $monthEnglishLong = $dt->format('F');
    $monthLocalizedLong = $monthListLong[$monthEnglishLong] ?? $monthEnglishLong;

    $monthListShort = $monthsShort[$lang] ?? $monthsShort['fr'];
    $monthEnglishShort = $dt->format('M');
    $monthLocalizedShort = $monthListShort[$monthEnglishShort] ?? $monthEnglishShort;

    $res = str_replace('µµµ', $monthLocalizedLong, $res);
    $res = str_replace('§§§', $monthLocalizedShort, $res);
    return $res;
}

/**
 * Exporte un objet JavaScript `window.I18N` avec les clés nécessaires côté client.
 */
function render_i18n_js_translations(): string {
    $keys = [
        'js.search_placeholder',
        'js.clear_search',
        'js.searching',
        'js.no_results',
        'js.conn_error',
        'js.clear_position',
        'track.search_button',
        'track.searching_colis',
        'common.loading',
    ];

    $translations = [];
    foreach ($keys as $k) {
        $translations[$k] = __($k);
    }

    $json = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    return '<script>window.I18N = ' . $json . ';</script>';
}

// Initialisation au chargement du module
init_i18n();

