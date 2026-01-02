<?php

namespace App\Services\Channels\Twitter;

use App\Models\Channels\TwitterProfile;
use App\Services\GlobalConfigService;
use App\Services\Http\OAuth1Client;
use Illuminate\Support\Facades\Log;

/**
 * Twitter API client for handling Twitter API interactions.
 * Provides OAuth 1.0a authentication and API methods.
 * 
 * @see app/models/channel/twitter_profile.rb (twitter_client method)
 */
class TwitterApiClient
{
    protected TwitterProfile $channel;
    protected OAuth1Client $oauthClient;

    public function __construct(TwitterProfile $channel)
    {
        $this->channel = $channel;
        $this->oauthClient = new OAuth1Client([
            'consumer_key' => GlobalConfigService::load('TWITTER_CONSUMER_KEY'),
            'consumer_secret' => GlobalConfigService::load('TWITTER_CONSUMER_SECRET'),
            'access_token' => $channel->twitter_access_token,
            'access_token_secret' => $channel->twitter_access_token_secret,
            'base_url' => 'https://api.twitter.com',
        ]);
    }

    /**
     * Send a direct message to a user.
     */
    public function sendDirectMessage(string $recipientId, string $message): array
    {
        try {
            $response = $this->oauthClient->post('/1.1/direct_messages/events/new.json', [
                'event' => [
                    'type' => 'message_create',
                    'message_create' => [
                        'target' => [
                            'recipient_id' => $recipientId
                        ],
                        'message_data' => [
                            'text' => $message
                        ]
                    ]
                ]
            ]);

            Log::info('Twitter direct message sent', [
                'channel_id' => $this->channel->id,
                'recipient_id' => $recipientId,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter direct message failed', [
                'channel_id' => $this->channel->id,
                'recipient_id' => $recipientId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Send a tweet reply.
     */
    public function sendTweetReply(string $replyToTweetId, string $tweet): array
    {
        try {
            $response = $this->oauthClient->post('/2/tweets', [
                'text' => $tweet,
                'reply' => [
                    'in_reply_to_tweet_id' => $replyToTweetId
                ]
            ]);

            Log::info('Twitter tweet reply sent', [
                'channel_id' => $this->channel->id,
                'reply_to_tweet_id' => $replyToTweetId,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter tweet reply failed', [
                'channel_id' => $this->channel->id,
                'reply_to_tweet_id' => $replyToTweetId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Register a webhook URL.
     */
    public function registerWebhook(string $url): array
    {
        try {
            $response = $this->oauthClient->post('/1.1/account_activity/all/webhooks.json', [
                'url' => $url
            ]);

            Log::info('Twitter webhook registered', [
                'channel_id' => $this->channel->id,
                'webhook_url' => $url,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter webhook registration failed', [
                'channel_id' => $this->channel->id,
                'webhook_url' => $url,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Unregister a webhook.
     */
    public function unregisterWebhook(string $webhookId): array
    {
        try {
            $response = $this->oauthClient->delete("/1.1/account_activity/all/webhooks/{$webhookId}.json");

            Log::info('Twitter webhook unregistered', [
                'channel_id' => $this->channel->id,
                'webhook_id' => $webhookId,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter webhook unregistration failed', [
                'channel_id' => $this->channel->id,
                'webhook_id' => $webhookId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Fetch existing webhooks.
     */
    public function fetchWebhooks(): array
    {
        try {
            $response = $this->oauthClient->get('/1.1/account_activity/all/webhooks.json');

            Log::info('Twitter webhooks fetched', [
                'channel_id' => $this->channel->id,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter webhooks fetch failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Create a subscription for the webhook.
     */
    public function createSubscription(): array
    {
        try {
            $response = $this->oauthClient->post('/1.1/account_activity/all/subscriptions.json');

            Log::info('Twitter subscription created', [
                'channel_id' => $this->channel->id,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter subscription creation failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Fetch subscriptions.
     */
    public function fetchSubscriptions(): array
    {
        try {
            $response = $this->oauthClient->get('/1.1/account_activity/all/subscriptions.json');

            Log::info('Twitter subscriptions fetched', [
                'channel_id' => $this->channel->id,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter subscriptions fetch failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Remove subscription for a user.
     */
    public function removeSubscription(string $userId): array
    {
        try {
            $response = $this->oauthClient->delete("/1.1/account_activity/all/subscriptions/{$userId}.json");

            Log::info('Twitter subscription removed', [
                'channel_id' => $this->channel->id,
                'user_id' => $userId,
                'response_status' => $response['status'] ?? null
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Twitter subscription removal failed', [
                'channel_id' => $this->channel->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}