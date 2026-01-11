<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Laravel\Sanctum\Guard as SanctumGuard;
use Laravel\Sanctum\Sanctum;

/**
 * Multi-Model Sanctum Guard
 * 
 * Extends Laravel Sanctum to support authentication of multiple model types
 * (User, AgentBot, PlatformApp) from the same personal_access_tokens table.
 * 
 * This guard checks the tokenable_type column to determine which model
 * to instantiate, allowing platform apps and agent bots to authenticate
 * alongside users using Sanctum tokens.
 * 
 * Usage:
 * - User tokens: Continue working as before
 * - AgentBot tokens: Authenticated as AgentBot instance
 * - PlatformApp tokens: Authenticated as PlatformApp instance
 */
class MultiModelSanctumGuard extends SanctumGuard
{
    /**
     * The supported authenticatable model classes.
     * 
     * @var array<string>
     */
    protected array $authenticatableModels = [
        \App\Models\User::class,
        \App\Models\AgentBot::class,
        \App\Models\PlatformApp::class,
    ];

    /**
     * Create a new guard instance.
     */
    public function __construct(
        AuthFactory $auth,
        protected $expiration = null,
        protected $provider = null,
        Request $request = null,
        protected $hash = true
    ) {
        parent::__construct($auth, $expiration, $provider, $request, $hash);
    }

    /**
     * Get the currently authenticated user.
     *
     * This method overrides Sanctum's default behavior to support multiple
     * model types by using the tokenable_type from the token.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // Check if we already resolved the user
        if ($this->user !== null) {
            return $this->user;
        }

        // Try to get token from request
        $token = $this->getTokenFromRequest();
        if (!$token) {
            return null;
        }

        // Find the token in database
        $accessToken = Sanctum::$personalAccessTokenModel::findToken($token);
        if (!$accessToken) {
            return null;
        }

        // Check if token is expired
        if ($this->expiration && $accessToken->created_at->lte(now()->subMinutes($this->expiration))) {
            return null;
        }

        // Get the tokenable (User, AgentBot, or PlatformApp)
        $tokenable = $accessToken->tokenable;
        
        // Verify the tokenable is an allowed authenticatable model
        if (!$this->isAuthenticatableModel($tokenable)) {
            return null;
        }

        // Check if token can be used (abilities check)
        if (method_exists($tokenable, 'tokenCan') && 
            !$tokenable->withAccessToken($accessToken)->tokenCan('*')) {
            return null;
        }

        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Set and return the authenticated user/bot/platform
        return $this->user = $tokenable->withAccessToken($accessToken);
    }

    /**
     * Get the token from the request.
     *
     * Supports both Bearer token and custom api_access_token header.
     *
     * @return string|null
     */
    protected function getTokenFromRequest(): ?string
    {
        // Check for Bearer token (standard Sanctum)
        if ($this->request && $this->request->bearerToken()) {
            return $this->request->bearerToken();
        }

        // Check for custom api_access_token header (Rails compatibility)
        if ($this->request) {
            return $this->request->header('api_access_token') 
                ?? $this->request->header('HTTP_API_ACCESS_TOKEN');
        }

        return null;
    }

    /**
     * Check if the given model is an allowed authenticatable.
     *
     * @param mixed $model
     * @return bool
     */
    protected function isAuthenticatableModel($model): bool
    {
        if (!is_object($model)) {
            return false;
        }

        $modelClass = get_class($model);
        
        return in_array($modelClass, $this->authenticatableModels, true);
    }

    /**
     * Set the authenticatable models that can be authenticated by this guard.
     *
     * @param array<string> $models
     * @return $this
     */
    public function setAuthenticatableModels(array $models): self
    {
        $this->authenticatableModels = $models;
        
        return $this;
    }

    /**
     * Get the authenticatable models.
     *
     * @return array<string>
     */
    public function getAuthenticatableModels(): array
    {
        return $this->authenticatableModels;
    }
}
