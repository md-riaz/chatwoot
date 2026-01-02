<?php

namespace App\Actions\SuperAdmin;

use App\Data\SuperAdmin\AccountUserData;
use App\Models\AccountUser;
use App\Enums\AccountUserRole;
use App\Enums\UserAvailability;
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
        $role = is_string($data->role) ? AccountUserRole::fromName($data->role) : AccountUserRole::from($data->role);
        $availability = is_int($data->availability) ? UserAvailability::from($data->availability) : UserAvailability::fromName($data->availability);

        return AccountUser::create([
            'user_id' => $data->user_id,
            'account_id' => $data->account_id,
            'role' => $role,
            'availability' => $availability,
            'settings' => $data->settings,
        ]);
    }
}