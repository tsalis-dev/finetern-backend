<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\GeminiAIService::class);
$res = $service->analyzeSkillGap('SMK Telkom', ['HTML (Intermediate)', 'React (Beginner)'], ['HTML', 'React', 'Node.js', 'PostgreSQL']);
echo json_encode($res);
