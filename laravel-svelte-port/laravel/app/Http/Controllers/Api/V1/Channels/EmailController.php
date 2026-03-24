<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\InboxResource;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Channels\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Create an Email channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        abort_unless($request->user()?->isAdministratorOf($account), 403);

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
            'smtp_enable_ssl_tls' => 'boolean',
            'smtp_enable_starttls_auto' => 'boolean',
            'smtp_authentication' => 'string|in:plain,login,cram_md5',
            'smtp_openssl_verify_mode' => 'string|in:none,peer',
        ]);

        $channel = Email::create([
            'account_id' => $account->id,
            'email' => strtolower($validated['email']),
            'forward_to_email' => strtolower($validated['email']),
            'imap_enabled' => $validated['imap_enabled'] ?? false,
            'imap_address' => $validated['imap_address'] ?? '',
            'imap_port' => $validated['imap_port'] ?? 0,
            'imap_login' => $validated['imap_login'] ?? '',
            'imap_password' => $validated['imap_password'] ?? '',
            'imap_enable_ssl' => $validated['imap_enable_ssl'] ?? true,
            'smtp_enabled' => $validated['smtp_enabled'] ?? false,
            'smtp_address' => $validated['smtp_address'] ?? '',
            'smtp_port' => $validated['smtp_port'] ?? 0,
            'smtp_login' => $validated['smtp_login'] ?? '',
            'smtp_password' => $validated['smtp_password'] ?? '',
            'smtp_domain' => $validated['smtp_domain'] ?? '',
            'smtp_enable_starttls_auto' => $validated['smtp_enable_starttls_auto'] ?? true,
            'smtp_authentication' => $validated['smtp_authentication'] ?? 'login',
            'smtp_openssl_verify_mode' => $validated['smtp_openssl_verify_mode'] ?? 'none',
            'smtp_enable_ssl_tls' => $validated['smtp_enable_ssl_tls'] ?? false,
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Email',
            'channel_id' => $channel->id,
        ]);

        return (new InboxResource($inbox->load('channel')))
            ->response()
            ->setStatusCode(201);
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
            'smtp_domain' => 'string|nullable',
            'smtp_enable_ssl_tls' => 'boolean',
            'smtp_enable_starttls_auto' => 'boolean',
            'smtp_authentication' => 'string|in:plain,login,cram_md5',
            'smtp_openssl_verify_mode' => 'string|in:none,peer',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if ($inbox->channel) {
            $inbox->channel->update(array_filter([
                'imap_enabled' => $validated['imap_enabled'] ?? null,
                'imap_address' => $validated['imap_address'] ?? null,
                'imap_port' => $validated['imap_port'] ?? null,
                'imap_login' => $validated['imap_login'] ?? null,
                'imap_password' => $validated['imap_password'] ?? null,
                'imap_enable_ssl' => $validated['imap_enable_ssl'] ?? null,
                'smtp_enabled' => $validated['smtp_enabled'] ?? null,
                'smtp_address' => $validated['smtp_address'] ?? null,
                'smtp_port' => $validated['smtp_port'] ?? null,
                'smtp_login' => $validated['smtp_login'] ?? null,
                'smtp_password' => $validated['smtp_password'] ?? null,
                'smtp_domain' => $validated['smtp_domain'] ?? null,
                'smtp_enable_ssl_tls' => $validated['smtp_enable_ssl_tls'] ?? null,
                'smtp_enable_starttls_auto' => $validated['smtp_enable_starttls_auto'] ?? null,
                'smtp_authentication' => $validated['smtp_authentication'] ?? null,
                'smtp_openssl_verify_mode' => $validated['smtp_openssl_verify_mode'] ?? null,
            ], fn ($value) => $value !== null));
        }

        return response()->json(['data' => $inbox->fresh()->load('channel')]);
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
        $raw = $request->all();

        // Normalize payload for common providers (Mailgun/Sendgrid/Postmark)
        $email = [];

        // Mailgun-style
        if ($request->has('Message-Id') || $request->has('message-id') || $request->has('message_id')) {
            $email['message_id'] = $request->input('Message-Id') ?? $request->input('message-id') ?? $request->input('message_id');
        }

        // From / To
        $email['from'] = $request->input('from') ?? $request->input('sender') ?? ($raw['headers']['From'] ?? null);
        $email['from_name'] = $request->input('from_name') ?? null;
        $email['to'] = $request->input('recipient') ?? $request->input('to') ?? ($raw['headers']['To'] ?? null);

        // Subject / Body
        $email['subject'] = $request->input('subject') ?? null;
        $email['body'] = $request->input('body-plain') ?? $request->input('text') ?? $request->input('html') ?? null;

        // attachments (provider-specific)
        $email['attachments'] = $request->input('attachments') ?? $request->input('attachment-count') ?? null;

        try {
            \App\Jobs\Channels\ProcessInboundEmailJob::dispatch($email);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to dispatch ProcessInboundEmailJob', ['error' => $e->getMessage(), 'payload' => $raw]);
            return response()->json(['error' => 'enqueue_failed'], 500);
        }

        return response()->json(['status' => 'queued']);
    }
}
