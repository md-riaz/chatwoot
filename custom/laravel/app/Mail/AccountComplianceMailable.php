<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountComplianceMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Account $account;
    public array $softDeletedUsers;

    public function __construct(Account $account, array $softDeletedUsers = [])
    {
        $this->account = $account;
        $this->softDeletedUsers = $softDeletedUsers;
    }

    public function build()
    {
        return $this->subject("Account deleted: {$this->account->name}")
            ->view('emails.account_deleted')
            ->with([
                'account' => $this->account,
                'softDeletedUsers' => $this->softDeletedUsers,
            ]);
    }
}
