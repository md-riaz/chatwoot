<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'query',
    ];

    protected $casts = [
        'query' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get contacts matching this segment's query
     */
    public function getContacts()
    {
        return $this->buildQuery()->get();
    }

    /**
     * Get count of contacts matching this segment's query
     */
    public function getContactsCount(): int
    {
        return $this->buildQuery()->count();
    }

    /**
     * Build the query based on segment filter rules
     */
    public function buildQuery()
    {
        $query = Contact::where('account_id', $this->account_id);

        if (empty($this->query)) {
            return $query;
        }

        foreach ($this->query as $filter) {
            $attribute = $filter['attribute_key'] ?? null;
            $filterOperator = $filter['filter_operator'] ?? 'equal_to';
            $values = $filter['values'] ?? [];

            if (!$attribute) {
                continue;
            }

            switch ($filterOperator) {
                case 'equal_to':
                    $query->where($attribute, $values[0] ?? null);
                    break;
                case 'not_equal_to':
                    $query->where($attribute, '!=', $values[0] ?? null);
                    break;
                case 'contains':
                    $query->where($attribute, 'LIKE', '%' . ($values[0] ?? '') . '%');
                    break;
                case 'does_not_contain':
                    $query->where($attribute, 'NOT LIKE', '%' . ($values[0] ?? '') . '%');
                    break;
                case 'is_present':
                    $query->whereNotNull($attribute);
                    break;
                case 'is_not_present':
                    $query->whereNull($attribute);
                    break;
                case 'is_greater_than':
                    $query->where($attribute, '>', $values[0] ?? 0);
                    break;
                case 'is_less_than':
                    $query->where($attribute, '<', $values[0] ?? 0);
                    break;
                case 'days_before':
                    $query->where($attribute, '<', now()->subDays((int) ($values[0] ?? 0)));
                    break;
            }
        }

        return $query;
    }
}
