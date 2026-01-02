<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasAccountRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, HasAccountRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'display_name',
        'phone_number',
        'avatar_url',
        'availability',
        'custom_attributes',
        'confirmation_token',
        'provider',
        'uid',
        'sso_auth_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'confirmation_token',
        'sso_auth_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'custom_attributes' => 'array',
            'availability' => 'integer',
        ];
    }

    /**
     * Get the accounts that the user belongs to.
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_users')
            ->withPivot('role', 'availability', 'settings', 'active_at')
            ->withTimestamps();
    }

    /**
     * Get the account user relationships.
     */
    public function accountUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccountUser::class);
    }

    /**
     * Get conversations for this user.
     */
    public function conversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Conversation::class, 'assignee_id');
    }

    /**
     * Get messages sent by this user.
     */
    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the inboxes that this user is assigned to.
     */
    public function assignedInboxes(): BelongsToMany
    {
        return $this->belongsToMany(Inbox::class, 'inbox_members')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include online users.
     */
    public function scopeOnline($query)
    {
        return $query->where('availability', 1);
    }

    /**
     * Check if the provided SSO auth token is valid for this user
     */
    public function validSsoAuthToken(?string $token): bool
    {
        if (empty($token) || empty($this->sso_auth_token)) {
            return false;
        }

        return hash_equals($this->sso_auth_token, $token);
    }

    /**
     * Find user by email (helper method for SAML)
     */
    public static function fromEmail(string $email): ?User
    {
        return static::where('email', $email)->first();
    }
}
