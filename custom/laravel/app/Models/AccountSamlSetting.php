<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSamlSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'sso_url',
        'certificate',
        'sp_entity_id',
        'idp_entity_id',
        'role_mappings',
    ];

    protected $casts = [
        'role_mappings' => 'array',
    ];

    protected $hidden = [
        'certificate',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
