<?php

// Load .env file
$envFile = __DIR__ . '/.env';
$youtubeLink = "https://www.youtube.com"; // fallback

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'YOUTUBE_LINK=') === 0) {
            $youtubeLink = trim(substr($line, strlen('YOUTUBE_LINK=')));
            break;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['youtubeLink' => $youtubeLink]);
?>