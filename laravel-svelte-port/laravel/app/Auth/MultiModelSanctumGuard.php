<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

/**
 * Multi-Model Sanctum Guard
 * 
 * Implements Laravel's Guard interface to support authentication of multiple model types
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
class MultiModelSanctumGuard implements Guard
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
     * The currently authenticated user.
     * 
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected $user;

    /**
     * The authentication factory implementation.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The number of minutes tokens should be allowed to remain valid.
     *
     * @var int|null
     */
    protected $expiration;

    /**
     * The provider name.
     *
     * @var string|null
     */
    protected $provider;

    /**
     * The current request instance.
     * 
     * @var \Illuminate\Http\Request|null
     */
    protected $request;

    /**
     * Create a new guard instance.
     */
    public function __construct(
        AuthFactory $auth,
        ?int $expiration = null,
        ?string $provider = null,
        ?Request $request = null
    ) {
        $this->auth = $auth;
        $this->expiration = $expiration;
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        $user = $this->user();
        return $user ? $user->getAuthIdentifier() : null;
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        // Token-based auth doesn't use credentials validation
        return false;
    }

    /**
     * Get the currently authenticated user.
     *
     * This method supports multiple model types by using the tokenable_type from the token.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        // Check if we already resolved the user
        if ($this->user !== null) {
            return $this->user;
        }

        // Get the request
        $request = $this->request ?? request();

        // First, try session-based authentication via web guard
        foreach (['web'] as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                if ($this->supportsTokens($user)) {
                    return $this->user = $user->withAccessToken(new \Laravel\Sanctum\TransientToken);
                }
                return $this->user = $user;
            }
        }

        // Try to get token from request
        $token = $this->getTokenFromRequest($request);
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

        // Check if token has an expiration date and it's passed
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        // Get the tokenable (User, AgentBot, or PlatformApp)
        $tokenable = $accessToken->tokenable;
        
        // Verify the tokenable exists and is an allowed authenticatable model
        if (!$tokenable || !$this->isAuthenticatableModel($tokenable)) {
            return null;
        }

        // Verify the tokenable supports tokens
        if (!$this->supportsTokens($tokenable)) {
            return null;
        }

        // Update last used timestamp
        $this->updateLastUsedAt($accessToken);

        // Set and return the authenticated user/bot/platform
        return $this->user = $tokenable->withAccessToken($accessToken);
    }

    /**
     * Get the token from the request.
     *
     * Supports both Bearer token and custom api_access_token header.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // Check for Bearer token (standard Sanctum)
        $token = $request->bearerToken();
        if ($token && $this->isValidBearerToken($token)) {
            return $token;
        }

        // Check for custom api_access_token header (Rails compatibility)
        $customToken = $request->header('api_access_token') 
            ?? $request->header('HTTP_API_ACCESS_TOKEN');
        
        return $customToken ?: null;
    }

    /**
     * Determine if the bearer token is in the correct format.
     *
     * @param  string|null  $token
     * @return bool
     */
    protected function isValidBearerToken(?string $token = null): bool
    {
        if (is_null($token)) {
            return false;
        }

        if (str_contains($token, '|')) {
            $model = new Sanctum::$personalAccessTokenModel;

            if ($model->getKeyType() === 'int') {
                [$id, $tokenPart] = explode('|', $token, 2);

                return ctype_digit($id) && !empty($tokenPart);
            }
        }

        return !empty($token);
    }

    /**
     * Determine if the tokenable model supports API tokens.
     *
     * @param  mixed  $tokenable
     * @return bool
     */
    protected function supportsTokens($tokenable = null): bool
    {
        return $tokenable && in_array(\Laravel\Sanctum\HasApiTokens::class, class_uses_recursive(
            get_class($tokenable)
        ));
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
     * Store the time the token was last used.
     *
     * @param  \Laravel\Sanctum\PersonalAccessToken  $accessToken
     * @return void
     */
    protected function updateLastUsedAt($accessToken): void
    {
        if (method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
            method_exists($accessToken->getConnection(), 'setRecordModificationState')) {
            $hasModifiedRecords = $accessToken->getConnection()->hasModifiedRecords();
            $accessToken->forceFill(['last_used_at' => now()])->save();

            $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
        } else {
            $accessToken->forceFill(['last_used_at' => now()])->save();
        }
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

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        
        return $this;
    }
}
