<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'custom_roles';

    protected $fillable = [
        'account_id',
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
