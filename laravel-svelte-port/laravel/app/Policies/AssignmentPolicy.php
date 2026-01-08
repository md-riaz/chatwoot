<?php

namespace App\Policies;

use App\Models\AssignmentPolicy as AssignmentPolicyModel;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AssignmentPolicyModel $policy): bool
    {
        return $user->accounts()->where('account_id', $policy->account_id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AssignmentPolicyModel $policy): bool
    {
        return $user->isAdministratorOf($policy->account);
    }

    public function delete(User $user, AssignmentPolicyModel $policy): bool
    {
        return $this->update($user, $policy);
    }
}
