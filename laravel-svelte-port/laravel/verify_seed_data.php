<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Companies count: " . App\Models\Company::count() . "\n";
echo "Conversations count: " . App\Models\Conversation::count() . "\n";
echo "Contacts count: " . App\Models\Contact::count() . "\n";
echo "Inboxes count: " . App\Models\Inbox::count() . "\n";
