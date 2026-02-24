<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

echo "--- Generating Test Token ---\n";
$user = User::first();
echo "User ID: " . $user->id . "\n";
$tokenInstance = $user->createToken('test-script');
$plainToken = $tokenInstance->plainTextToken;
echo "Plain Token: " . $plainToken . "\n";

echo "\n--- Inspecting the Token Record ---\n";
$dbToken = PersonalAccessToken::find($tokenInstance->accessToken->id);
echo "Tokenable Type: " . $dbToken->tokenable_type . "\n";
echo "Tokenable ID: " . $dbToken->tokenable_id . "\n";

echo "\n--- Attempting Authentication via Auth Guard ---\n";
$request = Request::create('/api/v1/accounts/1/agents', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $plainToken);

$guard = auth()->guard('sanctum');
$guard->setRequest($request);

$authenticatedUser = $guard->user();

if ($authenticatedUser) {
    echo "Guard authenticated user ID: " . $authenticatedUser->id . " (" . get_class($authenticatedUser) . ")\n";
} else {
    echo "Guard failed to authenticate user.\n";
}

echo "Done.\n";
