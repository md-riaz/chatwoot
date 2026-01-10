<?php

/**
 * Test script to check accounts API and user counts
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Account;
use App\Models\User;
use App\Models\AccountUser;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Accounts API Data...\n\n";

try {
    // Check accounts with user counts
    $accounts = Account::withCount(['users', 'inboxes', 'conversations', 'contacts'])->get();
    
    echo "=== ACCOUNTS WITH COUNTS ===\n";
    foreach ($accounts as $account) {
        echo "Account ID: {$account->id}\n";
        echo "Name: {$account->name}\n";
        echo "Users Count: {$account->users_count}\n";
        echo "Status: {$account->status->getName()} ({$account->status->value})\n";
        echo "Created: {$account->created_at}\n";
        echo "---\n";
    }
    
    echo "\n=== USERS ===\n";
    $users = User::all();
    foreach ($users as $user) {
        echo "User ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Email: {$user->email}\n";
        echo "Type: {$user->type}\n";
        echo "---\n";
    }
    
    echo "\n=== ACCOUNT USERS RELATIONSHIPS ===\n";
    $accountUsers = AccountUser::with(['user', 'account'])->get();
    foreach ($accountUsers as $accountUser) {
        echo "AccountUser ID: {$accountUser->id}\n";
        echo "Account: {$accountUser->account->name} (ID: {$accountUser->account_id})\n";
        echo "User: {$accountUser->user->name} (ID: {$accountUser->user_id})\n";
        echo "Role: {$accountUser->role->name}\n";
        echo "---\n";
    }
    
    echo "\n=== TESTING USERS RELATIONSHIP ===\n";
    foreach ($accounts as $account) {
        $directUsers = $account->users()->get();
        echo "Account '{$account->name}' has {$directUsers->count()} users via relationship:\n";
        foreach ($directUsers as $user) {
            echo "  - {$user->name} ({$user->email})\n";
        }
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}