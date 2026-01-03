<?php

namespace App\Data\Agent;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CapacityData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        public int $overall_capacity,

        #[IntegerType]
        #[Min(1)]
        public ?int $exclude_older_than_hours = null,

        #[ArrayType]
        /** @var string[] */
        public array $excluded_labels = [],
    ) {}

    public static function rules(): array
    {
        return [
            'overall_capacity' => ['required', 'integer', 'min:1'],
            'exclude_older_than_hours' => ['nullable', 'integer', 'min:1'],
            'excluded_labels' => ['array'],
            'excluded_labels.*' => ['string'],
        ];
    }
}