<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('role', 'student')
    ->whereHas('profile', function($q) { 
        $q->where('school', 'like', '%Telkom Malang%'); 
    })->first();

if ($user) {
    echo "Email: " . $user->email . "\n";
    echo "Password (Default): password123\n";
    echo "Nama: " . $user->name . "\n";
} else {
    echo "Not found\n";
}
