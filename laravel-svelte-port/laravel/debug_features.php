<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = App\Models\Account::find(1);
if ($account) {
    echo "Account ID: " . $account->id . "\n";
    echo "Account Name: " . $account->name . "\n";
    echo "Feature Flags (bits): " . $account->feature_flags . "\n";
    echo "Enabled Features:\n";
    foreach ($account->getEnabledFeatures() as $feature) {
        echo " - " . $feature . "\n";
    }
} else {
    echo "Account not found\n";
}
