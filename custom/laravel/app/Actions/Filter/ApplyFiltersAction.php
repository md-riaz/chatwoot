<?php

namespace App\Actions\Filter;

use App\Repositories\Filter\FilterRepository;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyFiltersAction
{
    use AsAction;

    private FilterRepository $filterRepository;

    public function __construct()
    {
        $this->filterRepository = new FilterRepository();
    }

    /**
     * Apply an advanced filter payload to the provided Eloquent query builder
     */
    public function handle(Builder $query, array $payload, int $accountId): Builder
    {
        foreach ($payload as $filter) {
            $attribute = $filter['attribute_key'] ?? null;
            $operator = $filter['filter_operator'] ?? 'equal_to';
            $values = $filter['values'] ?? [];
            $customType = $filter['custom_attribute_type'] ?? null;

            if (!$attribute) {
                continue;
            }

            // Normalize values
            $values = is_array($values) ? array_values($values) : [$values];
            $values = array_filter(array_map(function ($v) {
                return is_string($v) ? trim($v) : $v;
            }, $values), fn($v) => $v !== null && $v !== '');

            $this->applyFilter($query, $attribute, $operator, $values, $customType);
        }

        return $query;
    }

    /**
     * Apply a single filter to the query
     */
    private function applyFilter(Builder $query, string $attribute, string $operator, array $values, ?string $customType): void
    {
        switch ($attribute) {
            case 'status':
            case 'priority':
            case 'inbox_id':
            case 'team_id':
                $this->applyStandardFilter($query, $attribute, $operator, $values);
                break;

            case 'assignee_id':
                $this->applyAssigneeFilter($query, $operator, $values);
                break;

            case 'labels':
                $this->applyLabelsFilter($query, $operator, $values);
                break;

            case 'content':
                $this->applyContentFilter($query, $operator, $values);
                break;

            default:
                $this->applyCustomAttributeFilter($query, $attribute, $operator, $values, $customType);
                break;
        }
    }

    /**
     * Apply standard field filters
     */
    private function applyStandardFilter(Builder $query, string $attribute, string $operator, array $values): void
    {
        if (empty($values)) {
            return;
        }

        if ($operator === 'not_equal_to') {
            $query->whereNotIn($attribute, $values);
        } else {
            $query->whereIn($attribute, $values);
        }
    }

    /**
     * Apply assignee filter
     */
    private function applyAssigneeFilter(Builder $query, string $operator, array $values): void
    {
        if (empty($values)) {
            return;
        }

        if ($operator === 'not_equal_to') {
            $query->whereNotIn('assignee_id', $values);
        } else {
            $query->whereIn('assignee_id', $values);
        }
    }

    /**
     * Apply labels filter
     */
    private function applyLabelsFilter(Builder $query, string $operator, array $values): void
    {
        if (empty($values)) {
            return;
        }

        if ($operator === 'not_equal_to') {
            $query->whereDoesntHave('labels', function ($q) use ($values) {
                $q->whereIn('title', $values);
            });
        } else {
            $query->whereHas('labels', function ($q) use ($values) {
                $q->whereIn('title', $values);
            });
        }
    }

    /**
     * Apply content filter
     */
    private function applyContentFilter(Builder $query, string $operator, array $values): void
    {
        if (empty($values)) {
            return;
        }

        $query->where(function ($q) use ($values, $operator) {
            foreach ($values as $v) {
                $v = mb_strtolower($v);
                if (in_array($operator, ['does_not_contain', 'not_equal_to'])) {
                    $q->whereDoesntHave('messages', function ($mq) use ($v) {
                        $mq->whereRaw("LOWER(content) LIKE ?", ["%{$v}%"]);
                    });
                } else {
                    $q->orWhereHas('messages', function ($mq) use ($v) {
                        $mq->whereRaw("LOWER(content) LIKE ?", ["%{$v}%"]);
                    });
                }
            }
        });
    }

    /**
     * Apply custom attribute filter
     */
    private function applyCustomAttributeFilter(Builder $query, string $attribute, string $operator, array $values, ?string $customType): void
    {
        if ($customType === 'contact' || $customType === 'conversation_attribute' || str_starts_with($attribute, 'custom_attributes')) {
            $key = $attribute;

            if ($operator === 'is_present') {
                $query->whereRaw("(custom_attributes ->> ?) IS NOT NULL", [$key]);
            } elseif ($operator === 'is_not_present') {
                $query->whereRaw("(custom_attributes ->> ?) IS NULL", [$key]);
            } elseif (in_array($operator, ['contains', 'does_not_contain'])) {
                if ($operator === 'does_not_contain') {
                    foreach ($values as $v) {
                        $query->whereRaw("LOWER(custom_attributes ->> ?) NOT LIKE ?", [$key, "%".mb_strtolower($v)."%"]);
                    }
                } else {
                    $query->where(function ($q) use ($key, $values) {
                        foreach ($values as $v) {
                            $q->orWhereRaw("LOWER(custom_attributes ->> ?) LIKE ?", [$key, "%".mb_strtolower($v)."%"]);
                        }
                    });
                }
            } elseif (in_array($operator, ['is_greater_than', 'is_less_than'])) {
                $comp = $operator === 'is_greater_than' ? '>' : '<';
                foreach ($values as $v) {
                    $query->whereRaw("(custom_attributes ->> ?)::numeric {$comp} ?", [$key, $v]);
                }
            } elseif ($operator === 'days_before') {
                $days = (int) ($values[0] ?? 0);
                $date = now()->subDays($days)->toDateString();
                $query->whereRaw("(custom_attributes ->> ?)::date < ?", [$key, $date]);
            } else {
                $query->where(function ($q) use ($key, $values) {
                    foreach ($values as $v) {
                        $q->orWhereRaw("(custom_attributes ->> ?) = ?", [$key, $v]);
                    }
                });
            }
        }
    }
}