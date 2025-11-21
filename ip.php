<?php
// Improved IP logger — must not output anything before header()
ini_set('display_errors', '0'); // don't print warnings/notices
date_default_timezone_set('UTC');

function getClientIPFromHeaders() {
    $candidates = [];

    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) $candidates[] = $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) $candidates[] = $_SERVER['HTTP_X_REAL_IP'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) $candidates[] = $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        foreach ($parts as $p) $candidates[] = $p;
    }
    if (!empty($_SERVER['REMOTE_ADDR'])) $candidates[] = $_SERVER['REMOTE_ADDR'];

    // prefer first public IPv4/IPv6
    foreach ($candidates as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }
    // fallback to first valid IP (may be private)
    foreach ($candidates as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return 'unknown';
}

// Read JSON POST (client may send public IP + navigator info)
$raw = file_get_contents('php://input');
$posted = json_decode($raw, true) ?: [];

$clientPublicIp = $posted['clientIp'] ?? null;
$ua_post = $posted['ua'] ?? null;
$platform = $posted['platform'] ?? null;
$languages = $posted['languages'] ?? null;
$screen_info = isset($posted['screen']) ? json_encode($posted['screen']) : null;
$timezone = $posted['timezone'] ?? null;

// Server-detected IP
$serverIp = getClientIPFromHeaders();
// Decide final IP (prefer client-sent public IP if provided)
$finalIp = $clientPublicIp ? $clientPublicIp : $serverIp;

$allHeaders = function_exists('getallheaders') ? getallheaders() : $_SERVER;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? ($ua_post ?? 'unknown');
$acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ($languages ?? 'unknown');

$logFile = __DIR__ . '/ip.txt';
$entry  = "==== " . date('Y-m-d H:i:s') . " ====\n";
$entry .= "ClientPublicIP (from client): " . ($clientPublicIp ?? 'none') . "\n";
$entry .= "ServerDetectedIP: " . $serverIp . "\n";
$entry .= "Mobile_IP_Address: " . $finalIp . "\n";
$entry .= "User-Agent: " . $userAgent . "\n";
$entry .= "Accept-Language: " . $acceptLanguage . "\n";
$entry .= "Platform: " . ($platform ?? 'unknown') . "\n";
$entry .= "Screen: " . ($screen_info ?? 'unknown') . "\n";
$entry .= "Timezone: " . ($timezone ?? 'unknown') . "\n";
$entry .= "All headers:\n" . print_r($allHeaders, true) . "\n\n";

file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);


?>
