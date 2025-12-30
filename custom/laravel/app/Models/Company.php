<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'name',
        'identifier',
        'domain',
        'website',
        'custom_attributes',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
