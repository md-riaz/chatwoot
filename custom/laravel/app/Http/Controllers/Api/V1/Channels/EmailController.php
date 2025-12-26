<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Create an Email channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'imap_enabled' => 'boolean',
            'imap_address' => 'required_if:imap_enabled,true|string',
            'imap_port' => 'required_if:imap_enabled,true|integer',
            'imap_login' => 'required_if:imap_enabled,true|string',
            'imap_password' => 'required_if:imap_enabled,true|string',
            'imap_enable_ssl' => 'boolean',
            'smtp_enabled' => 'boolean',
            'smtp_address' => 'required_if:smtp_enabled,true|string',
            'smtp_port' => 'required_if:smtp_enabled,true|integer',
            'smtp_login' => 'required_if:smtp_enabled,true|string',
            'smtp_password' => 'required_if:smtp_enabled,true|string',
            'smtp_domain' => 'string',
            'smtp_enable_starttls_auto' => 'boolean',
            'smtp_authentication' => 'string|in:plain,login,cram_md5',
            'smtp_openssl_verify_mode' => 'string|in:none,peer',
        ]);

        // Create the inbox with Email channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Email',
        ]);

        // Create email channel with IMAP/SMTP settings
        // Would create a Channels\Email model

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update Email channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Email', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'imap_enabled' => 'boolean',
            'imap_address' => 'string',
            'imap_port' => 'integer',
            'imap_login' => 'string',
            'imap_password' => 'string',
            'imap_enable_ssl' => 'boolean',
            'smtp_enabled' => 'boolean',
            'smtp_address' => 'string',
            'smtp_port' => 'integer',
            'smtp_login' => 'string',
            'smtp_password' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Test IMAP connection.
     */
    public function testImap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'imap_address' => 'required|string',
            'imap_port' => 'required|integer',
            'imap_login' => 'required|string',
            'imap_password' => 'required|string',
            'imap_enable_ssl' => 'boolean',
        ]);

        // Test IMAP connection
        $success = true; // Would actually test the connection

        return response()->json(['success' => $success]);
    }

    /**
     * Test SMTP connection.
     */
    public function testSmtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'smtp_address' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_login' => 'required|string',
            'smtp_password' => 'required|string',
        ]);

        // Test SMTP connection
        $success = true; // Would actually test the connection

        return response()->json(['success' => $success]);
    }

    /**
     * Process incoming email (webhook from email service).
     */
    public function inbound(Request $request): JsonResponse
    {
        // Process incoming email
        // Create or update conversation
        // Create message

        return response()->json(['status' => 'received']);
    }
}
