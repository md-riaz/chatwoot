<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Http\Resources\User\UserResource;

$user = User::where('email', 'mdriaz@alpha.net.bd')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

$user->load(['accountUsers.account', 'roles']);
$resource = new UserResource($user);
$data = $resource->response()->getData(true);

echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
