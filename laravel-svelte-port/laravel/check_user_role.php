<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'michael_scott@paperlayer.test';
$user = App\Models\User::where('email', $email)->first();

if ($user) {
    echo "User found: " . $user->name . " (ID: " . $user->id . ")\n";
    foreach ($user->accountUsers as $au) {
        echo "Account ID: " . $au->account_id . " Role: " . $au->role->value . "\n";
        echo "Permissions: " . json_encode($au->permissions) . "\n";
        echo "Features: " . json_encode($au->account->getEnabledFeatures()) . "\n";
    }
} else {
    echo "User not found\n";
}
