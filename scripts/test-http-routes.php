<?php
// scripts/test-http-routes.php
$urls = [
    '/' => ['status' => 200, 'needle' => 'Pava Shipping'],
    '/en' => ['status' => 200, 'needle' => 'lang="en"'],
    '/de' => ['status' => 200, 'needle' => 'lang="de"'],
    '/nl' => ['status' => 200, 'needle' => 'lang="nl"'],
    '/fr/track' => ['status' => 200, 'needle' => 'lang="fr"'],
    '/en/track' => ['status' => 200, 'needle' => 'lang="en"'],
    '/de/services' => ['status' => 200, 'needle' => 'lang="de"'],
    '/nl/contact' => ['status' => 200, 'needle' => 'lang="nl"'],
    '/track?lang=de' => ['status' => 200, 'needle' => 'lang="de"'],
    '/login' => ['status' => 200, 'needle' => 'data-lang-switcher'],
];

$allOk = true;
echo "HTTP Route & Rendering Tests (http://127.0.0.1:8000):\n";
foreach ($urls as $uri => $expected) {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 3,
            'ignore_errors' => true,
        ]
    ]);
    $html = @file_get_contents("http://127.0.0.1:8000" . $uri, false, $ctx);
    $statusLine = $http_response_header[0] ?? '';
    preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $m);
    $code = (int)($m[1] ?? 0);
    
    $statusOk = ($code === $expected['status']);
    $needleOk = ($html !== false && str_contains($html, $expected['needle']));
    
    if ($statusOk && $needleOk) {
        echo "  [OK] $uri -> HTTP $code (Contient '{$expected['needle']}')\n";
    } else {
        echo "  [FAIL] $uri -> HTTP $code (Attendu {$expected['status']}, Contient '{$expected['needle']}': " . ($needleOk ? 'OUI' : 'NON') . ")\n";
        $allOk = false;
    }
}

if ($allOk) {
    echo "\nToutes les routes HTTP localisées fonctionnent parfaitement !\n";
} else {
    exit(1);
}
