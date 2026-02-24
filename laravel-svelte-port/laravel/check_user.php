<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Account;
use App\Models\AccountUser;

$users = User::all();
foreach ($users as $user) {
    echo "User: " . $user->name . " (ID: " . $user->id . ", Email: " . $user->email . ", Type: " . $user->type . ")\n";
    $accountUsers = AccountUser::where('user_id', $user->id)->get();
    foreach ($accountUsers as $au) {
        $account = Account::find($au->account_id);
        echo "  Account: " . ($account ? $account->name : 'Unknown') . " (ID: " . $au->account_id . ", Role: " . $au->role->value . ")\n";
    }
}
