<?php

/**
 * Test script to check accounts API with authentication
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Laravel\Sanctum\Sanctum;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Accounts API with Authentication...\n\n";

try {
    // Get the SuperAdmin user
    $superAdmin = User::where('type', 'SuperAdmin')->first();
    
    if (!$superAdmin) {
        echo "No SuperAdmin user found!\n";
        exit(1);
    }
    
    echo "Found SuperAdmin: {$superAdmin->name} ({$superAdmin->email})\n";
    
    // Create a token for the user
    $token = $superAdmin->createToken('test-token')->plainTextToken;
    echo "Created token: {$token}\n\n";
    
    // Test the API endpoint
    $url = 'http://127.0.0.1:8000/api/v1/super_admin/accounts?page=1&per_page=20';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: {$httpCode}\n";
    echo "Response:\n";
    echo json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n";
    
    // Clean up the token
    $superAdmin->tokens()->delete();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}