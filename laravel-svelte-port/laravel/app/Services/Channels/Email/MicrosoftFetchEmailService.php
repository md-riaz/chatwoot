<?php

namespace App\Services\Channels\Email;

use App\Models\Channels\Email;
use App\Services\Auth\MicrosoftRefreshOauthTokenService;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

/**
 * Microsoft OAuth-enabled email fetch service.
 * Handles IMAP connection with OAuth2 authentication for Outlook/Exchange.
 * 
 * @see app/services/imap/microsoft_fetch_email_service.rb
 */
class MicrosoftFetchEmailService extends BaseEmailFetchService
{
    protected MicrosoftRefreshOauthTokenService $oauthService;

    public function __construct(Email $channel)
    {
        parent::__construct($channel);
        $this->oauthService = new MicrosoftRefreshOauthTokenService($channel);
    }

    /**
     * Fetch emails using OAuth2 authentication.
     */
    public function fetchEmails(string $folder = 'INBOX', int $limit = 50): array
    {
        if (empty($this->channel->provider_config['access_token'])) {
            Log::warning('Microsoft OAuth access token not available', ['channel_id' => $this->channel->id]);
            return [];
        }

        try {
            $client = $this->getOAuthImapClient();
            $client->connect();

            $inbox = $client->getFolder($folder);
            $messages = $inbox->messages()
                ->unseen()
                ->limit($limit)
                ->get();

            $emails = [];
            foreach ($messages as $message) {
                $emails[] = $this->parseEmail($message);
            }

            return $emails;
        } catch (\Exception $e) {
            Log::error('Microsoft IMAP OAuth fetch failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get IMAP client configured for OAuth2 authentication.
     */
    protected function getOAuthImapClient(): Client
    {
        $accessToken = $this->oauthService->getAccessToken();
        
        if (!$accessToken) {
            throw new \RuntimeException('Unable to obtain valid Microsoft OAuth access token');
        }

        $cm = new ClientManager();
        
        return $cm->make([
            'host' => 'outlook.office365.com',
            'port' => 993,
            'encryption' => 'ssl',
            'validate_cert' => true,
            'username' => $this->channel->email,
            'password' => $accessToken,
            'authentication' => 'oauth',
            'protocol' => 'imap',
        ]);
    }

    /**
     * Test OAuth IMAP connection.
     */
    public function testConnection(): array
    {
        try {
            $client = $this->getOAuthImapClient();
            $client->connect();
            $folders = $client->getFolders();

            return [
                'success' => true,
                'message' => 'Microsoft OAuth IMAP connection successful',
                'folders' => array_map(fn($f) => $f->name, $folders->toArray()),
            ];
        } catch (\Exception $e) {
            Log::error('Microsoft OAuth IMAP connection test failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }
}