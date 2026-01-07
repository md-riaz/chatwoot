<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();

if ($user) {
    echo "User found:\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Created At (raw): " . $user->created_at . "\n";
    echo "Created At (ISO): " . $user->created_at?->toISOString() . "\n";
    
    // Test the transformation like in the controller
    $transformed = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'created_at' => $user->created_at?->toISOString(),
    ];
    
    echo "\nTransformed data:\n";
    echo json_encode($transformed, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No users found\n";
}