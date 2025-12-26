<?php

namespace App\Http\Requests\Account;

use App\Data\Account\AccountData;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy check: $this->user()->can('create', Account::class);
    }

    public function rules(): array
    {
        return AccountData::rules();
    }
}
