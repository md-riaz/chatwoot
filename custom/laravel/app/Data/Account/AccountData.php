<?php

namespace App\Data\Account;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AccountData extends Data
{
    public function __construct(
        public int|Optional $id,
        #[Required, StringType, Max(255)]
        public string $name,
        #[Required, StringType, Max(10)]
        public string $locale,
        #[Nullable, StringType]
        public ?string $domain,
        #[Nullable, Email]
        public ?string $support_email,
        public array|Optional $settings,
        public array|Optional $features,
        public array|Optional $limits,
        #[Required, In([0, 1])]
        public int $status = 1,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'domain' => ['nullable', 'string', 'unique:accounts,domain'],
            'support_email' => ['nullable', 'email'],
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
