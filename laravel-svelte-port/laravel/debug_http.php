<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$consoleKernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

class DebugAuthMiddleware {
    public function handle($request, \Closure $next) {
        echo "Inside Pipeline - Before Next\n";
        echo "Guard sanctum check before: " . (auth()->guard('sanctum')->check() ? "PASS" : "FAIL") . "\n";
        try {
            $response = $next($request);
            echo "Inside Pipeline - After Next\n";
            echo "Guard sanctum check after: " . (auth()->guard('sanctum')->check() ? "PASS" : "FAIL") . "\n";
            return $response;
        } catch (\Exception $e) {
            echo "Exception caught in pipeline: " . get_class($e) . " - " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
            throw $e;
        }
    }
}

echo "--- Generating Test Token ---\n";
$user = \App\Models\User::first();
echo "User ID: " . $user->id . "\n";
$tokenInstance = $user->createToken('test-script-3');
$plainToken = $tokenInstance->plainTextToken;
echo "Plain Token: " . $plainToken . "\n";

echo "\n--- Attempting Full App HTTP Request ---\n";
$request = \Illuminate\Http\Request::create('/api/v1/accounts/1/agents', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $plainToken);
$request->headers->set('Accept', 'application/json');

echo "Pre-run Guard Check: ";
$guard = auth()->guard('sanctum');
$guard->setRequest($request);
if ($guard->check()) {
    echo "PASS (User ID: " . $guard->user()->id . ")\n";
} else {
    echo "FAIL\n";
}

$kernel->prependMiddleware(DebugAuthMiddleware::class);
$response = $kernel->handle($request);

echo "Response Status: " . $response->getStatusCode() . "\n";
echo "Response Content: " . $response->getContent() . "\n";

$kernel->terminate($request, $response);
echo "Done.\n";
