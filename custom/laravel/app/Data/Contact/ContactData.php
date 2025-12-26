<?php

namespace App\Data\Contact;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ContactData extends Data
{
    public function __construct(
        public int|Optional $id,
        #[Required]
        public int $account_id,
        #[Nullable, StringType, Max(255)]
        public ?string $name,
        #[Nullable, Email]
        public ?string $email,
        #[Nullable, StringType, Max(50)]
        public ?string $phone_number,
        #[Nullable, StringType]
        public ?string $identifier,
        #[Nullable]
        public ?string $avatar_url,
        public array|Optional $custom_attributes,
        public array|Optional $additional_attributes,
    ) {}

    public static function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'identifier' => ['nullable', 'string'],
        ];
    }
}
