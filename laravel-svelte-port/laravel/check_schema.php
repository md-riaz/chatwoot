<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "account_users columns:\n";
print_r(Schema::getColumnListing('account_users'));

echo "\nusers columns:\n";
print_r(Schema::getColumnListing('users'));
