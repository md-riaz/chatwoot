<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    protected $model = Email::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'email' => $this->faker->safeEmail(),
            'forward_to_email' => $this->faker->unique()->safeEmail(),
            'imap_address' => 'imap.gmail.com',
            'imap_port' => 993,
            'imap_login' => $this->faker->safeEmail(),
            'imap_password' => $this->faker->password(),
            'imap_enabled' => true,
            'imap_enable_ssl' => true,
            'smtp_address' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_login' => $this->faker->safeEmail(),
            'smtp_password' => $this->faker->password(),
            'smtp_enabled' => true,
            'smtp_domain' => $this->faker->domainName(),
            'smtp_enable_starttls_auto' => true,
            'smtp_authentication' => 'plain',
            'smtp_openssl_verify_mode' => 'none',
            'smtp_enable_ssl_tls' => false,
            'provider_config' => [],
            'provider' => null,
            'verified_for_sending' => false,
        ];
    }

    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'google',
            'imap_address' => 'imap.gmail.com',
            'smtp_address' => 'smtp.gmail.com',
            'provider_config' => [
                'access_token' => $this->faker->uuid(),
                'refresh_token' => $this->faker->uuid(),
            ],
        ]);
    }

    public function microsoft(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'microsoft',
            'imap_address' => 'outlook.office365.com',
            'smtp_address' => 'smtp.office365.com',
            'provider_config' => [
                'access_token' => $this->faker->uuid(),
                'refresh_token' => $this->faker->uuid(),
            ],
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_for_sending' => true,
        ]);
    }
}