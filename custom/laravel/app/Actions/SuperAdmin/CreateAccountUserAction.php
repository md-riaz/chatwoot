<?php

namespace App\Actions\SuperAdmin;

use App\Data\SuperAdmin\AccountUserData;
use App\Models\AccountUser;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAccountUserAction
{
    use AsAction;

    public function handle(AccountUserData $data): AccountUser
    {
        // Check if relationship already exists
        $existing = AccountUser::where('user_id', $data->user_id)
            ->where('account_id', $data->account_id)
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('User is already associated with this account');
        }

        // Convert role name to integer if needed
        $role = $data->role;
        if (is_string($role)) {
            $roleMap = ['agent' => 1, 'admin' => 2];
            $role = $roleMap[$role] ?? 1;
        }

        return AccountUser::create([
            'user_id' => $data->user_id,
            'account_id' => $data->account_id,
            'role' => $role,
            'availability' => $data->availability ?? 1,
            'settings' => $data->settings,
        ]);
    }
}