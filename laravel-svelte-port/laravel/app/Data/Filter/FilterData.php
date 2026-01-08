<?php

namespace App\Data\Filter;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class FilterData extends Data
{
    public function __construct(
        #[Required]
        #[StringType]
        public string $attribute_key,

        #[Required]
        #[StringType]
        #[In(['equal_to', 'not_equal_to', 'contains', 'does_not_contain', 'is_present', 'is_not_present', 'is_greater_than', 'is_less_than', 'days_before'])]
        public string $filter_operator,

        #[Required]
        #[ArrayType]
        public array $values,

        #[StringType]
        public ?string $custom_attribute_type = null,
    ) {}

    public static function rules(): array
    {
        return [
            'attribute_key' => ['required', 'string'],
            'filter_operator' => [
                'required', 
                'string', 
                'in:equal_to,not_equal_to,contains,does_not_contain,is_present,is_not_present,is_greater_than,is_less_than,days_before'
            ],
            'values' => ['required', 'array'],
            'custom_attribute_type' => ['nullable', 'string', 'in:contact,conversation_attribute'],
        ];
    }
}