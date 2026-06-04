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
}

require __DIR__ . '/../public/index.php';
