<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SERVER['VERCEL_URL']) || isset($_ENV['VERCEL'])) {
    $tmpPaths = [
        '/tmp/storage/logs',
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/cache',
        '/tmp/storage/framework/sessions',
        '/tmp/bootstrap/cache'
    ];
    foreach ($tmpPaths as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
    
    // Fix routing paths for Laravel on Vercel
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
}

require __DIR__ . '/../public/index.php';
