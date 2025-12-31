<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Partial port of the Rails FilterService. This class provides helpers for
 * building complex filter queries used across the UI. Implementation is
 * intentionally incremental — we implement the key helpers and leave full
 * parity for follow-up work.
 */
class FilterService
{
    /**
     * Apply an advanced filter payload to the provided Eloquent query builder.
     * Returns the same builder for chaining.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $payload
     * @param int $accountId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyFilters(\Illuminate\Database\Eloquent\Builder $query, array $payload, int $accountId)
    {
        foreach ($payload as $filter) {
            $attribute = $filter['attribute_key'] ?? null;
            $operator = $filter['filter_operator'] ?? 'equal_to';
            $values = $filter['values'] ?? [];
            $customType = $filter['custom_attribute_type'] ?? null;

            if (! $attribute) {
                continue;
            }

            // Normalize values
            $values = is_array($values) ? array_values($values) : [$values];
            $values = array_filter(array_map(function ($v) {
                return is_string($v) ? trim($v) : $v;
            }, $values), fn($v) => $v !== null && $v !== '');

            switch ($attribute) {
                case 'status':
                case 'priority':
                case 'inbox_id':
                case 'team_id':
                    if (empty($values)) {
                        break;
                    }

                    if ($operator === 'not_equal_to') {
                        $query->whereNotIn($attribute, $values);
                    } else {
                        $query->whereIn($attribute, $values);
                    }
                    break;

                case 'assignee_id':
                    if (empty($values)) {
                        break;
                    }

                    if ($operator === 'not_equal_to') {
                        $query->whereNotIn('assignee_id', $values);
                    } else {
                        $query->whereIn('assignee_id', $values);
                    }
                    break;

                case 'labels':
                    if (empty($values)) {
                        break;
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
                    break;

                case 'content':
                    if (empty($values)) {
                        break;
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
                    break;

                default:
                    // Treat as custom attribute on conversations or contacts depending on custom_attribute_type
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
                            $date = Carbon::now()->subDays($days)->toDateString();
                            $query->whereRaw("(custom_attributes ->> ?)::date < ?", [$key, $date]);
                        } else {
                            $query->where(function ($q) use ($key, $values) {
                                foreach ($values as $v) {
                                    $q->orWhereRaw("(custom_attributes ->> ?) = ?", [$key, $v]);
                                }
                            });
                        }
                    }
                    break;
            }
        }

        return $query;
    }
}
