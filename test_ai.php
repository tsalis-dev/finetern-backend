<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\GeminiAIService::class);
$res = $service->calculateMatchRate(['React JS (Advanced)'], ['React JS', 'Node.js']);
echo json_encode($res);
