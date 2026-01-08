<?php

namespace App\Actions\Account;

use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use App\Mail\AccountComplianceMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAccountAction
{
    use AsAction;

    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    public function handle(Account $account): bool
    {
        // Soft-delete orphaned users and collect info for compliance notice
        $softDeletedUsers = [];

        foreach ($account->users as $user) {
            $otherAccounts = $user->accounts()->where('account_id', '!=', $account->id)->count();

            if ($otherAccounts === 0) {
                $originalEmail = $user->email;
                $user->email = "{$originalEmail}-deleted.com";
                $user->save();

                try {
                    $user->delete();
                } catch (\Throwable $e) {
                    Log::warning('DeleteAccountAction: user soft-delete failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }

                $softDeletedUsers[] = [
                    'id' => (string) $user->id,
                    'original_email' => $originalEmail,
                ];

                Log::info("Soft deleted user {$user->id} with email {$originalEmail}");
            }
        }

        // Send compliance notification to account administrators
        $adminEmails = $account->users()->administrators()->pluck('email')->filter()->unique()->values()->all();

        if (! empty($adminEmails)) {
            try {
                Mail::to($adminEmails)->queue(new AccountComplianceMailable($account, $softDeletedUsers));
            } catch (\Throwable $e) {
                Log::warning('DeleteAccountAction: sending compliance mail failed', ['error' => $e->getMessage()]);
            }
        }

        // Trigger event (kept for compatibility) and delete the account via repository
        // event(new AccountDeleted($account));

        return $this->accountRepository->delete($account->id);
    }
}
