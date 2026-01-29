<?php

namespace App\Services\Contact;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * ContactFilterService - Rails parity implementation
 * 
 * Handles advanced filtering of contacts with support for:
 * - Multiple filter conditions with AND/OR operators
 * - Standard attributes (name, email, phone_number, identifier, created_at, last_activity_at, blocked, labels)
 * - Additional attributes stored in JSON (country_code, city, company, referer)
 * - Custom attributes
 * 
 * Filter operators supported:
 * - equal_to, not_equal_to
 * - contains, does_not_contain
 * - is_present, is_not_present
 * - is_greater_than, is_less_than
 * - days_before
 * - starts_with
 */
class ContactFilterService
{
    private const RESULTS_PER_PAGE = 15;

    /**
     * Filter configuration matching Rails filter_keys.yml
     */
    private const FILTER_CONFIG = [
        'name' => [
            'attribute_type' => 'standard',
            'data_type' => 'text_case_insensitive',
            'filter_operators' => ['equal_to', 'not_equal_to', 'contains', 'does_not_contain'],
        ],
        'phone_number' => [
            'attribute_type' => 'standard',
            'data_type' => 'text',
            'filter_operators' => ['equal_to', 'not_equal_to', 'contains', 'does_not_contain', 'starts_with'],
        ],
        'email' => [
            'attribute_type' => 'standard',
            'data_type' => 'text_case_insensitive',
            'filter_operators' => ['equal_to', 'not_equal_to', 'contains', 'does_not_contain'],
        ],
        'identifier' => [
            'attribute_type' => 'standard',
            'data_type' => 'text_case_insensitive',
            'filter_operators' => ['equal_to', 'not_equal_to'],
        ],
        'country_code' => [
            'attribute_type' => 'additional_attributes',
            'data_type' => 'text_case_insensitive',
            'filter_operators' => ['equal_to', 'not_equal_to'],
        ],
        'city' => [
            'attribute_type' => 'additional_attributes',
            'data_type' => 'text_case_insensitive',
            'filter_operators' => ['equal_to', 'not_equal_to', 'contains', 'does_not_contain'],
        ],
        'company' => [
            'attribute_type' => 'additional_attributes',
            'data_type' => 'text_case_insensitive',
            'filter_operators' => ['equal_to', 'not_equal_to', 'contains', 'does_not_contain'],
        ],
        'labels' => [
            'attribute_type' => 'standard',
            'data_type' => 'labels',
            'filter_operators' => ['equal_to', 'not_equal_to', 'is_present', 'is_not_present'],
        ],
        'created_at' => [
            'attribute_type' => 'standard',
            'data_type' => 'date',
            'filter_operators' => ['is_greater_than', 'is_less_than', 'days_before'],
        ],
        'last_activity_at' => [
            'attribute_type' => 'standard',
            'data_type' => 'date',
            'filter_operators' => ['is_greater_than', 'is_less_than', 'days_before'],
        ],
        'blocked' => [
            'attribute_type' => 'standard',
            'data_type' => 'boolean',
            'filter_operators' => ['equal_to', 'not_equal_to'],
        ],
        'referer' => [
            'attribute_type' => 'additional_attributes',
            'data_type' => 'text',
            'filter_operators' => ['equal_to', 'not_equal_to', 'contains', 'does_not_contain'],
        ],
    ];

    private int $accountId;
    private array $payload;
    private ?string $label;
    private int $page;
    private string $sortAttr;
    private string $sortDirection;

    public function __construct(
        int $accountId,
        array $payload = [],
        ?string $label = null,
        int $page = 1,
        string $sortAttr = 'last_activity_at',
        string $sortDirection = 'desc'
    ) {
        $this->accountId = $accountId;
        $this->payload = $payload;
        $this->label = $label;
        $this->page = $page;
        $this->sortAttr = $sortAttr;
        $this->sortDirection = $sortDirection;
    }

    /**
     * Perform the filter operation (matches Rails interface)
     */
    public function perform(): array
    {
        $query = Contact::where('account_id', $this->accountId);

        // Apply label filter if provided
        if ($this->label) {
            $query->whereHas('labels', function ($q) {
                $q->where('title', $this->label);
            });
        }

        // Build filter conditions from payload
        $query = $this->buildFilterQuery($query);

        // Apply sorting
        $query->orderBy($this->sortAttr, $this->sortDirection);

        // Paginate results
        $contacts = $query->paginate(self::RESULTS_PER_PAGE, ['*'], 'page', $this->page);

        return [
            'contacts' => $contacts,
            'count' => $contacts->total(),
        ];
    }

    /**
     * Apply filters to an existing query (for backward compatibility)
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        $this->payload = $filters;
        return $this->buildFilterQuery($query);
    }

    /**
     * Build filter query from payload conditions
     */
    private function buildFilterQuery(Builder $query): Builder
    {
        if (empty($this->payload)) {
            return $query;
        }

        $isFirstCondition = true;

        foreach ($this->payload as $index => $filter) {
            $attributeKey = $filter['attribute_key'] ?? null;
            $filterOperator = $filter['filter_operator'] ?? 'equal_to';
            $values = $filter['values'] ?? [];
            $queryOperator = strtoupper($filter['query_operator'] ?? 'and');

            // Skip if no attribute key
            if (!$attributeKey) {
                continue;
            }

            // Skip if values are required but not present
            if (!in_array($filterOperator, ['is_present', 'is_not_present']) && empty($values)) {
                continue;
            }

            // Get filter config
            $config = self::FILTER_CONFIG[$attributeKey] ?? null;

            // Validate operator is allowed for this attribute
            if ($config && !in_array($filterOperator, $config['filter_operators'])) {
                Log::warning("Invalid operator '{$filterOperator}' for attribute '{$attributeKey}'");
                continue;
            }

            // Build the condition
            $condition = function ($q) use ($attributeKey, $filterOperator, $values, $config) {
                $this->applyFilterCondition($q, $attributeKey, $filterOperator, $values, $config);
            };

            // Apply with correct query operator (AND/OR)
            if ($isFirstCondition) {
                $query->where($condition);
                $isFirstCondition = false;
            } elseif ($queryOperator === 'OR') {
                $query->orWhere($condition);
            } else {
                $query->where($condition);
            }
        }

        return $query;
    }

    /**
     * Apply a single filter condition to the query
     */
    private function applyFilterCondition(
        Builder $query,
        string $attributeKey,
        string $filterOperator,
        array $values,
        ?array $config
    ): void {
        $attributeType = $config['attribute_type'] ?? 'standard';
        $dataType = $config['data_type'] ?? 'text';

        // Handle different attribute types
        if ($attributeType === 'additional_attributes') {
            $this->applyAdditionalAttributeFilter($query, $attributeKey, $filterOperator, $values, $dataType);
        } elseif ($dataType === 'labels') {
            $this->applyLabelsFilter($query, $filterOperator, $values);
        } elseif ($dataType === 'date') {
            $this->applyDateFilter($query, $attributeKey, $filterOperator, $values);
        } elseif ($dataType === 'boolean') {
            $this->applyBooleanFilter($query, $attributeKey, $filterOperator, $values);
        } elseif ($dataType === 'text_case_insensitive') {
            $this->applyTextCaseInsensitiveFilter($query, $attributeKey, $filterOperator, $values);
        } else {
            $this->applyStandardTextFilter($query, $attributeKey, $filterOperator, $values);
        }
    }

    /**
     * Apply filter on additional_attributes JSON column
     */
    private function applyAdditionalAttributeFilter(
        Builder $query,
        string $attributeKey,
        string $filterOperator,
        array $values,
        string $dataType
    ): void {
        $jsonPath = "additional_attributes->{$attributeKey}";
        $value = $this->prepareValue($values[0] ?? '', $attributeKey, $dataType);

        switch ($filterOperator) {
            case 'equal_to':
                if ($dataType === 'text_case_insensitive') {
                    $query->whereRaw("LOWER(additional_attributes->>'$attributeKey') = ?", [strtolower($value)]);
                } else {
                    $query->whereRaw("additional_attributes->>'$attributeKey' = ?", [$value]);
                }
                break;
            case 'not_equal_to':
                if ($dataType === 'text_case_insensitive') {
                    $query->where(function ($q) use ($attributeKey, $value) {
                        $q->whereRaw("LOWER(additional_attributes->>'$attributeKey') != ?", [strtolower($value)])
                          ->orWhereNull("additional_attributes->$attributeKey");
                    });
                } else {
                    $query->where(function ($q) use ($attributeKey, $value) {
                        $q->whereRaw("additional_attributes->>'$attributeKey' != ?", [$value])
                          ->orWhereNull("additional_attributes->$attributeKey");
                    });
                }
                break;
            case 'contains':
                $query->whereRaw("additional_attributes->>'$attributeKey' ILIKE ?", ["%{$value}%"]);
                break;
            case 'does_not_contain':
                $query->where(function ($q) use ($attributeKey, $value) {
                    $q->whereRaw("additional_attributes->>'$attributeKey' NOT ILIKE ?", ["%{$value}%"])
                      ->orWhereNull("additional_attributes->$attributeKey");
                });
                break;
            case 'is_present':
                $query->whereNotNull("additional_attributes->$attributeKey");
                break;
            case 'is_not_present':
                $query->whereNull("additional_attributes->$attributeKey");
                break;
        }
    }

    /**
     * Apply labels filter (tags relationship)
     */
    private function applyLabelsFilter(Builder $query, string $filterOperator, array $values): void
    {
        switch ($filterOperator) {
            case 'equal_to':
                $query->whereHas('labels', function ($q) use ($values) {
                    $q->whereIn('title', $values);
                });
                break;
            case 'not_equal_to':
                $query->whereDoesntHave('labels', function ($q) use ($values) {
                    $q->whereIn('title', $values);
                });
                break;
            case 'is_present':
                $query->has('labels');
                break;
            case 'is_not_present':
                $query->doesntHave('labels');
                break;
        }
    }

    /**
     * Apply date filter
     */
    private function applyDateFilter(Builder $query, string $attributeKey, string $filterOperator, array $values): void
    {
        $value = $values[0] ?? null;

        switch ($filterOperator) {
            case 'is_greater_than':
                $query->where($attributeKey, '>', $value);
                break;
            case 'is_less_than':
                $query->where($attributeKey, '<', $value);
                break;
            case 'days_before':
                $daysAgo = now()->subDays((int) $value)->toDateString();
                $query->where($attributeKey, '<', $daysAgo);
                break;
        }
    }

    /**
     * Apply boolean filter
     */
    private function applyBooleanFilter(Builder $query, string $attributeKey, string $filterOperator, array $values): void
    {
        $value = filter_var($values[0] ?? false, FILTER_VALIDATE_BOOLEAN);

        switch ($filterOperator) {
            case 'equal_to':
                $query->where($attributeKey, $value);
                break;
            case 'not_equal_to':
                $query->where($attributeKey, !$value);
                break;
        }
    }

    /**
     * Apply case-insensitive text filter
     */
    private function applyTextCaseInsensitiveFilter(
        Builder $query,
        string $attributeKey,
        string $filterOperator,
        array $values
    ): void {
        $value = strtolower($values[0] ?? '');

        switch ($filterOperator) {
            case 'equal_to':
                if (count($values) === 1) {
                    $query->whereRaw("LOWER({$attributeKey}) = ?", [$value]);
                } else {
                    $valuesLower = array_map('strtolower', $values);
                    $placeholders = implode(',', array_fill(0, count($valuesLower), '?'));
                    $query->whereRaw("LOWER({$attributeKey}) IN ({$placeholders})", $valuesLower);
                }
                break;
            case 'not_equal_to':
                if (count($values) === 1) {
                    $query->whereRaw("LOWER({$attributeKey}) != ?", [$value]);
                } else {
                    $valuesLower = array_map('strtolower', $values);
                    $placeholders = implode(',', array_fill(0, count($valuesLower), '?'));
                    $query->whereRaw("LOWER({$attributeKey}) NOT IN ({$placeholders})", $valuesLower);
                }
                break;
            case 'contains':
                $query->whereRaw("LOWER({$attributeKey}) LIKE ?", ["%{$value}%"]);
                break;
            case 'does_not_contain':
                $query->where(function ($q) use ($attributeKey, $value) {
                    $q->whereRaw("LOWER({$attributeKey}) NOT LIKE ?", ["%{$value}%"])
                      ->orWhereNull($attributeKey);
                });
                break;
            case 'is_present':
                $query->whereNotNull($attributeKey)->where($attributeKey, '!=', '');
                break;
            case 'is_not_present':
                $query->where(function ($q) use ($attributeKey) {
                    $q->whereNull($attributeKey)->orWhere($attributeKey, '=', '');
                });
                break;
        }
    }

    /**
     * Apply standard text filter (case-sensitive, e.g., phone_number)
     */
    private function applyStandardTextFilter(
        Builder $query,
        string $attributeKey,
        string $filterOperator,
        array $values
    ): void {
        $value = $this->prepareValue($values[0] ?? '', $attributeKey);

        switch ($filterOperator) {
            case 'equal_to':
                $query->whereIn($attributeKey, $values);
                break;
            case 'not_equal_to':
                $query->whereNotIn($attributeKey, $values);
                break;
            case 'contains':
                $query->where($attributeKey, 'LIKE', "%{$value}%");
                break;
            case 'does_not_contain':
                $query->where(function ($q) use ($attributeKey, $value) {
                    $q->where($attributeKey, 'NOT LIKE', "%{$value}%")
                      ->orWhereNull($attributeKey);
                });
                break;
            case 'starts_with':
                $query->where($attributeKey, 'LIKE', "{$value}%");
                break;
            case 'is_present':
                $query->whereNotNull($attributeKey)->where($attributeKey, '!=', '');
                break;
            case 'is_not_present':
                $query->where(function ($q) use ($attributeKey) {
                    $q->whereNull($attributeKey)->orWhere($attributeKey, '=', '');
                });
                break;
        }
    }

    /**
     * Prepare value for filtering (Rails parity: phone_number, country_code handling)
     */
    private function prepareValue(string $value, string $attributeKey, string $dataType = 'text'): string
    {
        if ($attributeKey === 'phone_number') {
            // Ensure phone number has + prefix
            return '+' . ltrim($value, '+');
        }

        if ($attributeKey === 'country_code') {
            // Country codes are stored lowercase
            return strtolower($value);
        }

        return $value;
    }
}