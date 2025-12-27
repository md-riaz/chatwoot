<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountSamlSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountSamlSetting>
 */
class AccountSamlSettingFactory extends Factory
{
    protected $model = AccountSamlSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'sso_url' => fake()->url(),
            'certificate' => '-----BEGIN CERTIFICATE-----\nMIIB...\n-----END CERTIFICATE-----',
            'sp_entity_id' => 'chatwoot-' . fake()->slug(),
            'idp_entity_id' => fake()->url(),
            'role_mappings' => [],
        ];
    }
}
