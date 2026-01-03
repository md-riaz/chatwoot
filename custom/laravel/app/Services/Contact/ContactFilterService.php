<?php

namespace App\Services\Contact;

use Illuminate\Database\Eloquent\Builder;

class ContactFilterService
{
    public function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if (!is_array($filter) || !isset($filter['attribute_key'], $filter['filter_operator'], $filter['values'])) {
                continue;
            }

            $attribute = $filter['attribute_key'];
            $operator = $filter['filter_operator'];
            $values = $filter['values'];

            $this->applyFilter($query, $attribute, $operator, $values);
        }

        return $query;
    }

    private function applyFilter(Builder $query, string $attribute, string $operator, array $values): void
    {
        switch ($attribute) {
            case 'email':
            case 'name':
            case 'phone_number':
            case 'identifier':
                $this->applyStringFilter($query, $attribute, $operator, $values);
                break;
            case 'created_at':
            case 'last_activity_at':
                $this->applyDateFilter($query, $attribute, $operator, $values);
                break;
        }
    }

    private function applyStringFilter(Builder $query, string $column, string $operator, array $values): void
    {
        switch ($operator) {
            case 'equal_to':
                $query->where($column, $values[0] ?? '');
                break;
            case 'not_equal_to':
                $query->where($column, '!=', $values[0] ?? '');
                break;
            case 'contains':
                $query->where($column, 'LIKE', '%' . ($values[0] ?? '') . '%');
                break;
            case 'is_present':
                $query->whereNotNull($column)->where($column, '!=', '');
                break;
            case 'is_not_present':
                $query->where(function ($q) use ($column) {
                    $q->whereNull($column)->orWhere($column, '');
                });
                break;
        }
    }

    private function applyDateFilter(Builder $query, string $column, string $operator, array $values): void
    {
        switch ($operator) {
            case 'is_greater_than':
                if (!empty($values[0])) {
                    $query->where($column, '>', $values[0]);
                }
                break;
            case 'is_less_than':
                if (!empty($values[0])) {
                    $query->where($column, '<', $values[0]);
                }
                break;
            case 'is_present':
                $query->whereNotNull($column);
                break;
            case 'is_not_present':
                $query->whereNull($column);
                break;
        }
    }
}