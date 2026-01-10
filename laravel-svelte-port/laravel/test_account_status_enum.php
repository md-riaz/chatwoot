<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Enums\AccountStatus;
use App\Models\Account;

// Test the enum functionality
echo "=== Account Status Enum Test ===\n\n";

// Test enum values
echo "1. Enum Values:\n";
echo "   ACTIVE: " . AccountStatus::ACTIVE->value . " ('" . AccountStatus::ACTIVE->getName() . "')\n";
echo "   SUSPENDED: " . AccountStatus::SUSPENDED->value . " ('" . AccountStatus::SUSPENDED->getName() . "')\n\n";

// Test fromString conversion
echo "2. String to Enum Conversion:\n";
$activeEnum = AccountStatus::fromString('active');
$suspendedEnum = AccountStatus::fromString('suspended');
echo "   'active' -> " . $activeEnum->value . " ('" . $activeEnum->getName() . "')\n";
echo "   'suspended' -> " . $suspendedEnum->value . " ('" . $suspendedEnum->getName() . "')\n\n";

// Test enum options
echo "3. Available Options:\n";
foreach (AccountStatus::options() as $key => $label) {
    echo "   $key: $label\n";
}
echo "\n";

// Test with Account model (if database is available)
try {
    echo "4. Account Model Test:\n";
    
    // Create a test account
    $account = new Account();
    $account->name = 'Test Account';
    $account->locale = 'en';
    $account->status = AccountStatus::ACTIVE;
    
    echo "   Created account with status: " . $account->status->getName() . " (DB value: " . $account->status->value . ")\n";
    
    // Test status checks
    echo "   Is active: " . ($account->status === AccountStatus::ACTIVE ? 'Yes' : 'No') . "\n";
    echo "   Is suspended: " . ($account->status === AccountStatus::SUSPENDED ? 'Yes' : 'No') . "\n";
    
    // Test setting from string
    $account->status = AccountStatus::fromString('suspended');
    echo "   After setting to 'suspended': " . $account->status->getName() . " (DB value: " . $account->status->value . ")\n";
    
} catch (Exception $e) {
    echo "   Database test skipped: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";