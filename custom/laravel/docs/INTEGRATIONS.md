# ClearLine Laravel Integration Guide

This document provides detailed information about each integration type in ClearLine Laravel.

---

## Table of Contents

1. [Channel Integrations](#channel-integrations)
   - [Web Widget](#web-widget)
   - [Email](#email)
   - [WhatsApp](#whatsapp)
   - [Facebook Messenger](#facebook-messenger)
   - [Twitter](#twitter)
   - [Telegram](#telegram)
   - [SMS (Twilio/Bandwidth)](#sms)
   - [LINE](#line)
   - [API Channel](#api-channel)
2. [Third-Party Integrations](#third-party-integrations)
   - [Slack](#slack)
   - [Dialogflow](#dialogflow)
   - [OpenAI](#openai)
   - [Linear](#linear)
   - [Shopify](#shopify)

---

## Channel Integrations

### Web Widget

**Purpose:** Embeddable chat widget for websites.

**Controller:** `Channels/WebWidgetController.php`

**Features:**
- Real-time chat with website visitors
- Pre-chat forms for lead capture
- Customizable appearance (colors, position)
- Working hours support
- CSAT surveys
- File attachments

**Setup Steps:**
1. Create a new inbox with channel type `Channel::WebWidget`
2. Get the website token from the inbox settings
3. Embed the widget script on your website:

```html
<script>
  (function(d,t) {
    var BASE_URL = "https://your-clearline-url.com";
    var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
    g.src = BASE_URL + "/packs/js/sdk.js";
    g.defer = true;
    g.async = true;
    s.parentNode.insertBefore(g, s);
    g.onload = function() {
      window.$clearline.run({
        websiteToken: 'YOUR_WEBSITE_TOKEN',
        baseUrl: BASE_URL
      })
    }
  })(document, "script");
</script>
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "web_widget", website_url: "https://..." }

GET  /api/v1/widget/config
POST /api/v1/widget/conversations
POST /api/v1/widget/messages
```

---

### Email

**Purpose:** Handle customer emails as conversations.

**Controller:** `Channels/EmailController.php`

**Features:**
- IMAP/SMTP integration
- Email threading
- HTML email rendering
- Attachments support
- Email forwarding

**Setup Steps:**
1. Create inbox with channel type `Channel::Email`
2. Configure IMAP settings for receiving emails
3. Configure SMTP settings for sending emails

**Configuration:**
```php
[
    'email' => 'support@company.com',
    'imap_address' => 'imap.gmail.com',
    'imap_port' => 993,
    'imap_login' => 'support@company.com',
    'imap_password' => 'encrypted_password',
    'imap_enable_ssl' => true,
    'smtp_address' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_login' => 'support@company.com',
    'smtp_password' => 'encrypted_password',
    'smtp_enable_starttls_auto' => true,
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "email", email: "support@company.com" }

POST /api/v1/webhooks/email     (incoming emails)
```

---

### WhatsApp

**Purpose:** Connect WhatsApp Business API for customer messaging.

**Controller:** `Channels/WhatsAppController.php`

**Providers Supported:**
- WhatsApp Business API (official)
- 360dialog
- Twilio WhatsApp

**Features:**
- Text and media messages
- Message templates
- Interactive messages (buttons, lists)
- Quick replies
- Read receipts

**Setup Steps:**
1. Set up WhatsApp Business API account
2. Create inbox with channel type `Channel::Whatsapp`
3. Configure webhook URL in WhatsApp provider

**Configuration:**
```php
[
    'phone_number' => '+1234567890',
    'provider' => 'official',  // or '360dialog', 'twilio'
    'api_key' => 'your_api_key',
    'business_account_id' => 'your_business_account_id',
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "whatsapp", phone_number: "+1234567890" }

POST /api/v1/webhooks/whatsapp  (incoming messages)
GET  /api/v1/accounts/{account}/inboxes/{inbox}/message_templates
POST /api/v1/accounts/{account}/inboxes/{inbox}/sync_templates
```

---

### Facebook Messenger

**Purpose:** Connect Facebook Pages for Messenger support.

**Controller:** `Channels/FacebookController.php`

**Features:**
- Facebook Page integration
- Messenger conversations
- Image/video messages
- Quick replies
- Message templates

**Setup Steps:**
1. Create Facebook App with Messenger permissions
2. Create inbox with channel type `Channel::FacebookPage`
3. Connect Facebook Page via OAuth
4. Configure webhook in Facebook Developer Console

**Configuration:**
```php
[
    'page_id' => 'your_page_id',
    'page_access_token' => 'your_page_access_token',
    'user_access_token' => 'your_user_access_token',
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "facebook", page_id: "..." }

GET  /api/v1/accounts/{account}/channels/facebook/callback (OAuth)
POST /api/v1/webhooks/facebook   (incoming messages)
```

---

### Twitter

**Purpose:** Handle Twitter Direct Messages as conversations.

**Controller:** `Channels/TwitterController.php`

**Features:**
- Direct Messages
- Quick replies
- Media messages
- Auto-response

**Setup Steps:**
1. Create Twitter Developer App
2. Create inbox with channel type `Channel::TwitterProfile`
3. Authorize Twitter account via OAuth
4. Configure Account Activity API webhook

**Configuration:**
```php
[
    'twitter_id' => 'your_twitter_user_id',
    'screen_name' => 'your_screen_name',
    'access_token' => 'your_access_token',
    'access_token_secret' => 'your_access_token_secret',
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "twitter", screen_name: "@company" }

GET  /api/v1/accounts/{account}/channels/twitter/callback
POST /api/v1/webhooks/twitter
```

---

### Telegram

**Purpose:** Connect Telegram Bot for customer support.

**Controller:** `Channels/TelegramController.php`

**Features:**
- Bot conversations
- Media messages
- Inline keyboards
- Quick replies

**Setup Steps:**
1. Create Telegram Bot via BotFather
2. Create inbox with channel type `Channel::Telegram`
3. Configure webhook URL with Telegram

**Configuration:**
```php
[
    'bot_token' => 'your_bot_token',
    'bot_username' => 'your_bot_username',
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "telegram", bot_token: "..." }

POST /api/v1/webhooks/telegram  (incoming messages)
```

---

### SMS

**Purpose:** SMS messaging via Twilio or Bandwidth.

**Controller:** `Channels/SmsController.php`

**Providers Supported:**
- Twilio
- Bandwidth

**Features:**
- SMS sending/receiving
- MMS support (Twilio)
- Phone number provisioning

**Setup Steps:**
1. Set up Twilio/Bandwidth account
2. Provision phone number
3. Create inbox with channel type `Channel::TwilioSms` or `Channel::BandwidthSms`
4. Configure webhook URL

**Configuration (Twilio):**
```php
[
    'phone_number' => '+1234567890',
    'account_sid' => 'your_account_sid',
    'auth_token' => 'your_auth_token',
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "sms", phone_number: "+1234567890", provider: "twilio" }

POST /api/v1/webhooks/sms  (incoming messages)
```

---

### LINE

**Purpose:** LINE Messaging API integration.

**Controller:** `Channels/LineController.php`

**Features:**
- LINE conversations
- Rich messages
- Stickers
- Image messages

**Setup Steps:**
1. Create LINE Messaging API channel
2. Create inbox with channel type `Channel::Line`
3. Configure webhook URL in LINE Developer Console

**Configuration:**
```php
[
    'channel_id' => 'your_channel_id',
    'channel_secret' => 'your_channel_secret',
    'channel_access_token' => 'your_access_token',
]
```

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "line", channel_id: "..." }

POST /api/v1/webhooks/line  (incoming messages)
```

---

### API Channel

**Purpose:** Custom API-based channel for programmatic integrations.

**Controller:** `Channels/ApiController.php`

**Features:**
- Programmatic message sending
- Custom integrations
- Webhook support
- Flexible payload format

**Use Cases:**
- Custom mobile apps
- Internal tools
- Third-party platform integrations
- IoT devices

**Setup Steps:**
1. Create inbox with channel type `Channel::Api`
2. Get API channel identifier
3. Use API to send/receive messages

**API Endpoints:**
```
POST /api/v1/accounts/{account}/inboxes
     channel: { type: "api" }

POST /api/v1/accounts/{account}/conversations
     inbox_id: <api_inbox_id>
     
POST /api/v1/accounts/{account}/conversations/{id}/messages
```

---

## Third-Party Integrations

### Slack

**Purpose:** Send notifications and sync conversations with Slack.

**Controller:** `Integrations/SlackController.php`

**Features:**
- New conversation notifications
- Conversation sync to Slack channels
- Reply from Slack
- Mention agents

**Setup Steps:**
1. Create Slack App with required permissions
2. Install app to workspace
3. Connect integration in ClearLine settings

**Configuration:**
```php
[
    'access_token' => 'xoxb-...',
    'team_id' => 'T...',
    'default_channel' => '#support',
]
```

**Events Subscribed:**
- `message.channels`
- `message.im`
- `app_mention`

**API Endpoints:**
```
GET  /api/v1/accounts/{account}/integrations/slack
POST /api/v1/accounts/{account}/integrations/slack
POST /api/v1/webhooks/slack/events
POST /api/v1/webhooks/slack/interactive
POST /api/v1/webhooks/slack/commands
```

---

### Dialogflow

**Purpose:** AI-powered chatbot for automated responses.

**Controller:** `Integrations/DialogflowController.php`

**Features:**
- Intent detection
- Entity extraction
- Context management
- Handoff to human agents
- Multi-language support

**Setup Steps:**
1. Create Dialogflow agent in Google Cloud
2. Create service account with Dialogflow API access
3. Download service account JSON key
4. Configure integration in ClearLine

**Configuration:**
```php
[
    'project_id' => 'your-gcp-project',
    'credentials' => '{ ... service account JSON ... }',
    'language_code' => 'en',
]
```

**Bot Behavior:**
1. Incoming message triggers Dialogflow detection
2. Bot responds with detected intent response
3. On handoff intent, conversation transferred to agent
4. Human agent takes over

**API Endpoints:**
```
GET  /api/v1/accounts/{account}/integrations/dialogflow
POST /api/v1/accounts/{account}/integrations/dialogflow
POST /api/v1/accounts/{account}/integrations/dialogflow/detect_intent
```

---

### OpenAI

**Purpose:** AI-powered assistance for agents.

**Controller:** `Integrations/OpenAIController.php`

**Features:**
- Reply suggestions
- Conversation summarization
- Sentiment analysis
- Message tone adjustment
- Auto-complete responses

**Setup Steps:**
1. Get OpenAI API key
2. Configure integration in ClearLine settings

**Configuration:**
```php
[
    'api_key' => 'sk-...',
    'model' => 'gpt-4',
    'max_tokens' => 500,
]
```

**Available Actions:**
- `suggest_reply` - Generate reply suggestions
- `summarize` - Summarize conversation
- `fix_spelling` - Fix spelling and grammar
- `expand` - Expand short message
- `shorten` - Make message more concise
- `change_tone` - Adjust message tone (formal/friendly)

**API Endpoints:**
```
GET  /api/v1/accounts/{account}/integrations/openai
POST /api/v1/accounts/{account}/integrations/openai
POST /api/v1/accounts/{account}/integrations/openai/actions/{action}
```

---

### Linear

**Purpose:** Create Linear issues from conversations.

**Controller:** `Integrations/LinearController.php`

**Features:**
- Create issues from conversations
- Link issues to conversations
- Sync issue status
- View linked issues

**Setup Steps:**
1. Create Linear OAuth application
2. Connect Linear account via OAuth
3. Select default team and project

**Configuration:**
```php
[
    'access_token' => 'lin_api_...',
    'team_id' => 'TEAM_ID',
    'project_id' => 'PROJECT_ID',
]
```

**API Endpoints:**
```
GET  /api/v1/accounts/{account}/integrations/linear
POST /api/v1/accounts/{account}/integrations/linear
POST /api/v1/accounts/{account}/integrations/linear/create_issue
GET  /api/v1/accounts/{account}/integrations/linear/linked_issues/{conversation}
POST /api/v1/accounts/{account}/integrations/linear/link_issue
DELETE /api/v1/accounts/{account}/integrations/linear/unlink_issue
```

---

### Shopify

**Purpose:** E-commerce integration for order lookup.

**Controller:** `Integrations/ShopifyController.php`

**Features:**
- Customer order history
- Order status lookup
- Product information
- Inventory check
- Customer info sync

**Setup Steps:**
1. Create Shopify App (custom or public)
2. Install app on Shopify store
3. Configure integration with API credentials

**Configuration:**
```php
[
    'shop_domain' => 'your-store.myshopify.com',
    'access_token' => 'shpat_...',
    'api_version' => '2024-01',
]
```

**Data Available:**
- Customer orders
- Order status and tracking
- Product details
- Customer information

**API Endpoints:**
```
GET  /api/v1/accounts/{account}/integrations/shopify
POST /api/v1/accounts/{account}/integrations/shopify
GET  /api/v1/accounts/{account}/integrations/shopify/orders/{contact}
GET  /api/v1/accounts/{account}/integrations/shopify/products
POST /api/v1/webhooks/shopify  (order updates)
```

---

## Adding New Integrations

To add a new integration:

1. **Create Controller:**
   ```bash
   php artisan make:controller Api/V1/Integrations/NewIntegrationController
   ```

2. **Create Service:**
   ```bash
   php artisan make:class Services/Integrations/NewIntegrationService
   ```

3. **Add Routes:**
   ```php
   // In routes/api.php
   Route::prefix('integrations/new_integration')->group(function () {
       Route::get('/', [NewIntegrationController::class, 'show']);
       Route::post('/', [NewIntegrationController::class, 'store']);
       Route::patch('/', [NewIntegrationController::class, 'update']);
       Route::delete('/', [NewIntegrationController::class, 'destroy']);
   });
   ```

4. **Add Webhook Route:**
   ```php
   // In routes/api.php (public webhooks section)
   Route::post('new_integration', [NewIntegrationController::class, 'webhook']);
   ```

5. **Add Integration Hook Model:**
   ```php
   // Create integrations_hooks record
   IntegrationHook::create([
       'account_id' => $accountId,
       'app_id' => 'new_integration',
       'settings' => [...],
   ]);
   ```

---

**Last Updated:** 2025-12-27
**Version:** 7.0.0
