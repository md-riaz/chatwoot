# Chatwoot Backend Architecture

> **Complete Guide to Chatwoot's Backend System**  
> This document explains the backend architecture, data flow, relationships, and implementation patterns to help developers understand and replicate similar functionality in any technology stack.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Architecture Patterns](#architecture-patterns)
4. [Core Domain Models](#core-domain-models)
5. [Data Flow & Request Lifecycle](#data-flow--request-lifecycle)
6. [Service Layer Architecture](#service-layer-architecture)
7. [Database Schema & Relationships](#database-schema--relationships)
8. [API Structure](#api-structure)
9. [Background Jobs & Async Processing](#background-jobs--async-processing)
10. [WebSocket & Real-time Communication](#websocket--real-time-communication)
11. [Authentication & Authorization](#authentication--authorization)
12. [Implementation Examples](#implementation-examples)
13. [Laravel Equivalent Examples](#laravel-equivalent-examples)
14. [Replicating in Other Stacks](#replicating-in-other-stacks)

---

## System Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ Vue.js SPA   │  │ Mobile Apps  │  │ Widget SDK   │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└────────────────────────────┬────────────────────────────────────┘
                             │
                ┌────────────┴────────────┐
                │   HTTP/REST API         │
                │   WebSocket (Cable)     │
                └────────────┬────────────┘
                             │
┌─────────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER                           │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    Controllers                            │   │
│  │  API::V1::Accounts::ConversationsController              │   │
│  │  API::V1::Accounts::MessagesController                   │   │
│  │  API::V1::Accounts::ContactsController                   │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│  ┌────────────────────────┴─────────────────────────────────┐   │
│  │                   Service Layer                          │   │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │   │
│  │  │   Builders   │  │   Services   │  │   Finders    │  │   │
│  │  │ - Message    │  │ - AutoAssign │  │ - Contact    │  │   │
│  │  │ - Contact    │  │ - Filter     │  │ - Convo      │  │   │
│  │  └──────────────┘  └──────────────┘  └──────────────┘  │   │
│  └────────────────────────┬─────────────────────────────────┘   │
│                           │                                      │
│  ┌────────────────────────┴─────────────────────────────────┐   │
│  │                   Domain Models                          │   │
│  │  Account → Inbox → Conversation → Message                │   │
│  │  Contact → ContactInbox                                  │   │
│  │  User → AccountUser → Team                               │   │
│  └────────────────────────┬─────────────────────────────────┘   │
└───────────────────────────┼──────────────────────────────────────┘
                            │
┌───────────────────────────┴──────────────────────────────────────┐
│                      DATA LAYER                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  PostgreSQL  │  │    Redis     │  │   S3/Storage │          │
│  │  (Primary DB)│  │  (Cache/Jobs)│  │ (Attachments)│          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└──────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                   INTEGRATION LAYER                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  WhatsApp    │  │   Email      │  │  Facebook    │          │
│  │  Twilio      │  │   Slack      │  │  Instagram   │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└──────────────────────────────────────────────────────────────────┘
```

### Key Components

1. **API Layer**: RESTful API endpoints organized by version and resource
2. **Service Layer**: Business logic encapsulation (Builders, Services, Finders)
3. **Domain Models**: ActiveRecord models with relationships and validations
4. **Background Jobs**: Sidekiq for async processing
5. **WebSocket**: ActionCable for real-time updates
6. **Channel Integrations**: External messaging platform adapters

---

## Technology Stack

### Core Technologies

```yaml
Backend Framework: Ruby on Rails 7.x
Language: Ruby 3.x
Database: PostgreSQL 13+
Cache/Queue: Redis 6+
Background Jobs: Sidekiq
WebSocket: ActionCable
Search: Elasticsearch (optional)
Storage: ActiveStorage (S3, Local, etc.)
Authentication: Devise Token Auth
Authorization: Pundit Policies
API: REST (JSON)
Testing: RSpec, FactoryBot
```

### Key Gems/Libraries

```ruby
# Core
gem 'rails', '~> 7.0'
gem 'pg'                      # PostgreSQL
gem 'redis'                   # Cache & Jobs
gem 'sidekiq'                 # Background processing

# Authentication & Authorization
gem 'devise'                  # Authentication
gem 'devise_token_auth'       # Token-based auth for API
gem 'pundit'                  # Authorization policies

# API & Serialization
gem 'jbuilder'                # JSON views
gem 'rack-cors'               # CORS handling

# Real-time
gem 'actioncable'             # WebSocket support

# Integrations
gem 'twilio-ruby'             # SMS
gem 'aws-sdk-s3'              # File storage
gem 'httparty'                # HTTP requests

# Utilities
gem 'acts-as-taggable-on'     # Labeling
gem 'kaminari'                # Pagination
gem 'flag_shih_tzu'           # Feature flags
```

---

## Architecture Patterns

### 1. MVC + Service Layer

Chatwoot follows Rails MVC with an additional service layer:

```
Controllers → Service Objects → Models → Database
              ↓
         (Business Logic)
```

**Pattern Benefits:**
- Controllers remain thin (routing & params)
- Services contain business logic
- Models focus on data & relationships
- Easy to test each layer independently

### 2. Builder Pattern

For complex object creation:

```ruby
# app/builders/conversation_builder.rb
class ConversationBuilder
  def initialize(params:, contact_inbox:)
    @params = params
    @contact_inbox = contact_inbox
  end

  def perform
    @conversation = create_conversation
    update_contact_last_seen
    dispatch_events
    @conversation
  end

  private

  def create_conversation
    Conversation.create!(
      account_id: @contact_inbox.inbox.account_id,
      inbox_id: @contact_inbox.inbox_id,
      contact_id: @contact_inbox.contact_id,
      contact_inbox_id: @contact_inbox.id,
      additional_attributes: @params[:additional_attributes]
    )
  end
end
```

### 3. Service Objects

Encapsulate business logic:

```ruby
# app/services/auto_assignment/assignment_service.rb
class AutoAssignment::AssignmentService
  def initialize(conversation:, allowed_agent_ids: [])
    @conversation = conversation
    @allowed_agent_ids = allowed_agent_ids
  end

  def perform
    return unless should_assign?
    
    agent = find_agent
    assign_agent(agent) if agent.present?
  end

  private

  def should_assign?
    @conversation.unassigned? && inbox.enable_auto_assignment?
  end

  def find_agent
    RoundRobinSelector.new(
      inbox: inbox,
      allowed_agent_ids: @allowed_agent_ids
    ).perform
  end
end
```

### 4. Finder Pattern

Query encapsulation:

```ruby
# app/finders/conversation_finder.rb
class ConversationFinder
  def initialize(current_user, params)
    @current_user = current_user
    @params = params
  end

  def perform
    conversations = base_query
    conversations = filter_by_status(conversations)
    conversations = filter_by_assignee(conversations)
    
    {
      conversations: conversations.limit(page_size),
      count: conversations.count
    }
  end

  private

  def base_query
    @current_user.account.conversations
      .includes(:inbox, :contact, :assignee)
  end
end
```

### 5. Concern Modules

Reusable model behaviors:

```ruby
# app/models/concerns/assignment_handler.rb
module AssignmentHandler
  extend ActiveSupport::Concern

  included do
    belongs_to :assignee, class_name: 'User', optional: true
    after_update :notify_assignee_change, if: :saved_change_to_assignee_id?
  end

  def assign_agent(agent)
    update!(assignee: agent)
  end

  private

  def notify_assignee_change
    # Broadcast event, send notification, etc.
  end
end
```

---

## Core Domain Models

### Entity Relationship Diagram

```
┌──────────────┐
│   Account    │ (Multi-tenant root)
└──────┬───────┘
       │
       ├─────────┬─────────┬─────────┬─────────┐
       │         │         │         │         │
   ┌───▼───┐ ┌──▼──┐  ┌───▼───┐ ┌───▼───┐ ┌──▼──┐
   │ Inbox │ │User │  │Contact│ │ Team  │ │Label│
   └───┬───┘ └──┬──┘  └───┬───┘ └───┬───┘ └─────┘
       │        │          │         │
       │        │          │         │
       │   ┌────▼─────┐    │         │
       │   │AccountUser│◄──┘         │
       │   └────┬─────┘               │
       │        │                     │
       ├────────┴─────────────────────┤
       │                              │
   ┌───▼────────┐              ┌─────▼────┐
   │ContactInbox│◄─────────────┤TeamMember│
   └───┬────────┘              └──────────┘
       │
   ┌───▼────────────┐
   │  Conversation  │
   └───┬────────────┘
       │
   ┌───▼────────┐
   │  Message   │
   └────────────┘
```

### Core Models Explained

#### 1. Account (Tenant)

```ruby
# == Schema Information
# Table: accounts
#   id                    :integer          PK
#   name                  :string           not null
#   domain                :string(100)
#   support_email         :string(100)
#   locale                :integer          default("en")
#   settings              :jsonb
#   feature_flags         :bigint           default(0)
#   status                :integer          default("active")
#   auto_resolve_duration :integer
#   limits                :jsonb
#   custom_attributes     :jsonb
#   created_at            :datetime
#   updated_at            :datetime

class Account < ApplicationRecord
  # Relationships
  has_many :users, through: :account_users
  has_many :account_users, dependent: :destroy
  has_many :inboxes, dependent: :destroy
  has_many :conversations, dependent: :destroy
  has_many :contacts, dependent: :destroy
  has_many :teams, dependent: :destroy
  has_many :labels, dependent: :destroy
  
  # Validations
  validates :name, presence: true
  validates :locale, inclusion: { in: AVAILABLE_LOCALES }
  
  # Feature Flags (using flag_shih_tzu)
  has_flags 1 => :custom_branding,
            2 => :inbox_management,
            3 => :email_continuity
            
  # Settings (JSONB column)
  # { auto_resolve_after: 40, timezone: 'UTC' }
end
```

**Purpose**: Multi-tenant isolation. Each account is a separate customer workspace.

**Key Patterns**:
- JSONB for flexible settings
- Feature flags for plan-based features
- Soft limits stored in JSONB

#### 2. User & AccountUser

```ruby
# == Schema Information
# Table: users
#   id                  :integer          PK
#   email               :string           not null, unique
#   encrypted_password  :string
#   name                :string
#   display_name        :string
#   avatar_url          :string
#   type                :string           (User, Contact, AgentBot)
#   availability        :integer          default("online")
#   custom_attributes   :jsonb
#   ui_settings         :jsonb
#   created_at          :datetime
#   updated_at          :datetime

class User < ApplicationRecord
  # Multi-account support via join table
  has_many :account_users, dependent: :destroy
  has_many :accounts, through: :account_users
  
  # Conversations
  has_many :assigned_conversations, 
           foreign_key: 'assignee_id',
           class_name: 'Conversation'
  
  # Authentication (Devise)
  devise :database_authenticatable, :registerable,
         :recoverable, :rememberable, :validatable,
         :confirmable, :trackable
  
  enum availability: { online: 0, offline: 1, busy: 2 }
end

# == Schema Information
# Table: account_users
#   id         :integer          PK
#   account_id :integer          not null, FK
#   user_id    :integer          not null, FK
#   role       :integer          default("agent")
#   inviter_id :integer          FK
#   created_at :datetime
#   updated_at :datetime

class AccountUser < ApplicationRecord
  belongs_to :account
  belongs_to :user
  belongs_to :inviter, class_name: 'User', optional: true
  
  enum role: { agent: 0, administrator: 1 }
  
  validates :account_id, uniqueness: { scope: :user_id }
end
```

**Purpose**: 
- Users can belong to multiple accounts
- Role-based access control per account
- Single sign-on across accounts

#### 3. Inbox (Channel)

```ruby
# == Schema Information
# Table: inboxes
#   id                      :integer          PK
#   account_id              :integer          not null, FK
#   name                    :string           not null
#   channel_id              :integer          not null, polymorphic FK
#   channel_type            :string           not null
#   enable_auto_assignment  :boolean          default(true)
#   greeting_enabled        :boolean          default(false)
#   greeting_message        :string
#   email_address           :string
#   working_hours_enabled   :boolean          default(false)
#   out_of_office_message   :string
#   timezone                :string           default("UTC")
#   created_at              :datetime
#   updated_at              :datetime

class Inbox < ApplicationRecord
  belongs_to :account
  belongs_to :channel, polymorphic: true
  
  # Channel types: Channel::WebWidget, Channel::Email, 
  #                Channel::Twilio::Sms, Channel::Api, etc.
  
  has_many :conversations, dependent: :destroy
  has_many :contact_inboxes, dependent: :destroy
  has_many :inbox_members, dependent: :destroy
  has_many :members, through: :inbox_members, source: :user
  
  validates :name, presence: true
  validates :account_id, presence: true
end
```

**Purpose**: Represents a communication channel (Website Widget, Email, WhatsApp, etc.)

**Polymorphic Association**: `channel` can be any channel type:
```ruby
# Channel::WebWidget
# Channel::Email  
# Channel::Twilio::Sms
# Channel::FacebookPage
# Channel::Api
```

#### 4. Contact

```ruby
# == Schema Information
# Table: contacts
#   id                 :integer          PK
#   account_id         :integer          not null, FK
#   name               :string
#   email              :string
#   phone_number       :string
#   identifier         :string
#   custom_attributes  :jsonb
#   additional_attributes :jsonb
#   last_activity_at   :datetime
#   created_at         :datetime
#   updated_at         :datetime

class Contact < ApplicationRecord
  belongs_to :account
  
  has_many :conversations, dependent: :destroy
  has_many :contact_inboxes, dependent: :destroy
  has_many :inboxes, through: :contact_inboxes
  has_many :messages, dependent: :destroy
  
  validates :account_id, presence: true
  validates :email, format: { with: URI::MailTo::EMAIL_REGEXP }, 
            allow_blank: true
  
  # Custom attributes stored as JSONB
  # { company: 'Acme', plan: 'enterprise', ltv: 5000 }
end
```

**Purpose**: Represents a customer/end-user across all channels.

#### 5. ContactInbox (Channel-specific Contact)

```ruby
# == Schema Information
# Table: contact_inboxes
#   id               :integer          PK
#   contact_id       :bigint           not null, FK
#   inbox_id         :bigint           not null, FK
#   source_id        :string           not null (external ID)
#   hmac_verified    :boolean          default(false)
#   pubsub_token     :string
#   created_at       :datetime
#   updated_at       :datetime

class ContactInbox < ApplicationRecord
  belongs_to :contact
  belongs_to :inbox
  
  has_many :conversations, dependent: :destroy
  
  validates :inbox_id, uniqueness: { scope: :source_id }
  validates :source_id, presence: true
  
  # source_id examples:
  # - WhatsApp: phone number (+1234567890)
  # - Email: email address
  # - Widget: UUID
end
```

**Purpose**: Links a contact to a specific inbox with channel-specific identifier.

**Why Needed**: Same person might contact via email, widget, and WhatsApp - each gets a ContactInbox.

#### 6. Conversation

```ruby
# == Schema Information
# Table: conversations
#   id                     :integer          PK
#   account_id             :integer          not null, FK
#   inbox_id               :integer          not null, FK
#   contact_id             :bigint           FK
#   contact_inbox_id       :bigint           FK
#   assignee_id            :integer          FK (User)
#   team_id                :bigint           FK
#   display_id             :integer          not null (per-account)
#   status                 :integer          default("open")
#   priority               :integer
#   identifier             :string
#   additional_attributes  :jsonb
#   custom_attributes      :jsonb
#   snoozed_until          :datetime
#   last_activity_at       :datetime         not null
#   agent_last_seen_at     :datetime
#   assignee_last_seen_at  :datetime
#   contact_last_seen_at   :datetime
#   first_reply_created_at :datetime
#   waiting_since          :datetime
#   uuid                   :uuid             not null, unique
#   created_at             :datetime
#   updated_at             :datetime

class Conversation < ApplicationRecord
  belongs_to :account
  belongs_to :inbox
  belongs_to :contact, optional: true
  belongs_to :contact_inbox, optional: true
  belongs_to :assignee, class_name: 'User', optional: true
  belongs_to :team, optional: true
  
  has_many :messages, dependent: :destroy
  has_many :labels, through: :conversation_labels
  
  enum status: { open: 0, resolved: 1, pending: 2, snoozed: 3 }
  enum priority: { low: 0, medium: 1, high: 2, urgent: 3 }
  
  validates :account_id, presence: true
  validates :inbox_id, presence: true
  validates :display_id, uniqueness: { scope: :account_id }
  
  before_create :set_display_id
  
  private
  
  def set_display_id
    self.display_id = account.conversations.maximum(:display_id).to_i + 1
  end
end
```

**Purpose**: A conversation thread between contact and agents.

**Key Fields**:
- `display_id`: User-friendly ID (per account: #1, #2, #3...)
- `uuid`: Global unique identifier
- `status`: Lifecycle state
- `last_activity_at`: For sorting/filtering

#### 7. Message

```ruby
# == Schema Information
# Table: messages
#   id                        :integer          PK
#   account_id                :integer          not null, FK
#   conversation_id           :integer          not null, FK
#   inbox_id                  :integer          not null, FK
#   sender_id                 :bigint           polymorphic FK
#   sender_type               :string           (User, Contact, AgentBot)
#   message_type              :integer          not null
#   content                   :text
#   content_type              :integer          default("text")
#   content_attributes        :json
#   private                   :boolean          default(false)
#   status                    :integer          default("sent")
#   source_id                 :text             (external message ID)
#   additional_attributes     :jsonb
#   processed_message_content :text
#   sentiment                 :jsonb
#   created_at                :datetime
#   updated_at                :datetime

class Message < ApplicationRecord
  belongs_to :account
  belongs_to :conversation
  belongs_to :inbox
  belongs_to :sender, polymorphic: true, optional: true
  
  has_many :attachments, dependent: :destroy
  
  enum message_type: { 
    incoming: 0,    # From contact
    outgoing: 1,    # From agent/bot
    activity: 2,    # System message
    template: 3     # Template message
  }
  
  enum content_type: {
    text: 0,
    input_text: 1,
    input_textarea: 2,
    input_email: 3,
    input_select: 4,
    cards: 5,
    form: 6,
    article: 7,
    incoming_email: 8,
    input_csat: 9,
    sticker: 10
  }
  
  enum status: {
    sent: 0,
    delivered: 1,
    read: 2,
    failed: 3
  }
  
  validates :account_id, presence: true
  validates :conversation_id, presence: true
  validates :message_type, presence: true
  
  # Callbacks
  after_create_commit :notify_via_websocket
  after_create_commit :send_email_notifications
  after_create :reopen_conversation, if: :incoming?
end
```

**Purpose**: Individual message in a conversation.

**Polymorphic Sender**: Can be User, Contact, or AgentBot.

---

## Data Flow & Request Lifecycle

### Example: Creating a New Message

```
┌─────────────┐
│   Client    │
│  (Vue SPA)  │
└──────┬──────┘
       │ POST /api/v1/accounts/{id}/conversations/{id}/messages
       │ { content: "Hello", message_type: "outgoing" }
       ▼
┌──────────────────────────────────────────────────────────────────┐
│ 1. ROUTING (config/routes.rb)                                    │
│    → Api::V1::Accounts::Conversations::MessagesController        │
└──────┬───────────────────────────────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────────┐
│ 2. CONTROLLER (messages_controller.rb)                           │
│    - Authenticate user (Devise Token Auth)                       │
│    - Authorize action (Pundit policy)                            │
│    - Load conversation                                           │
│    - Call service/builder                                        │
└──────┬───────────────────────────────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────────┐
│ 3. BUILDER/SERVICE (Messages::MessageBuilder)                    │
│    - Validate params                                             │
│    - Create message record                                       │
│    - Process attachments                                         │
│    - Update conversation (last_activity_at)                      │
│    - Trigger notifications                                       │
└──────┬───────────────────────────────────────────────────────────┘
       │
       ├───────────────────┬────────────────────┬─────────────────┐
       ▼                   ▼                    ▼                 ▼
  ┌────────┐      ┌─────────────┐      ┌──────────┐    ┌─────────────┐
  │Database│      │  WebSocket  │      │Background│    │ Integration │
  │ Write  │      │  Broadcast  │      │   Jobs   │    │   Webhook   │
  └────────┘      └─────────────┘      └──────────┘    └─────────────┘
       │                   │                    │                 │
       │                   │                    │                 │
  [Message]          [Cable Notif]        [Email Job]     [Slack Push]
   Created            → Other agents       [Push Notif]      
                      → Contact (widget)
```

### Code Flow Example

**1. Route Definition**

```ruby
# config/routes.rb
namespace :api, defaults: { format: 'json' } do
  namespace :v1 do
    resources :accounts do
      scope module: :accounts do
        resources :conversations do
          scope module: :conversations do
            resources :messages, only: [:index, :create, :update, :destroy]
          end
        end
      end
    end
  end
end
```

**2. Controller**

```ruby
# app/controllers/api/v1/accounts/conversations/messages_controller.rb
class Api::V1::Accounts::Conversations::MessagesController < Api::V1::Accounts::BaseController
  before_action :set_conversation
  
  def create
    authorize @conversation, :create_message?
    
    @message = Messages::MessageBuilder.new(
      Current.user,
      @conversation,
      message_params
    ).perform
    
    render json: @message, status: :created
  end
  
  private
  
  def set_conversation
    @conversation = Current.account.conversations.find(params[:conversation_id])
  end
  
  def message_params
    params.require(:message).permit(:content, :message_type, :private, attachments: [])
  end
end
```

**3. Builder/Service**

```ruby
# app/builders/messages/message_builder.rb
class Messages::MessageBuilder
  def initialize(user, conversation, params)
    @user = user
    @conversation = conversation
    @params = params
  end
  
  def perform
    ActiveRecord::Base.transaction do
      @message = create_message
      attach_files if @params[:attachments].present?
      update_conversation
      dispatch_events
      trigger_notifications
    end
    
    @message
  end
  
  private
  
  def create_message
    @conversation.messages.create!(
      account_id: @conversation.account_id,
      inbox_id: @conversation.inbox_id,
      sender: @user,
      content: @params[:content],
      message_type: @params[:message_type] || :outgoing,
      private: @params[:private] || false
    )
  end
  
  def attach_files
    @params[:attachments].each do |file|
      @message.attachments.create!(file: file)
    end
  end
  
  def update_conversation
    @conversation.update!(
      last_activity_at: Time.current,
      assignee_last_seen_at: Time.current
    )
  end
  
  def dispatch_events
    # Broadcast via WebSocket
    ActionCable.server.broadcast(
      "conversation:#{@conversation.id}",
      { event: 'message.created', data: @message.as_json }
    )
  end
  
  def trigger_notifications
    # Queue background jobs
    SendEmailNotificationJob.perform_later(@message.id)
    SendPushNotificationJob.perform_later(@message.id)
    TriggerWebhookJob.perform_later(@message.id, 'message_created')
  end
end
```

---

## Service Layer Architecture

### Service Object Types

#### 1. Builders

**Purpose**: Complex object creation with related operations

```ruby
# app/builders/conversation_builder.rb
class ConversationBuilder
  def perform
    create_conversation
    create_initial_message
    assign_agent
    trigger_automation
  end
end
```

**When to Use**:
- Creating objects with dependencies
- Multi-step creation process
- Need transaction safety

#### 2. Services

**Purpose**: Business logic execution

```ruby
# app/services/contacts/merge_service.rb
class Contacts::MergeService
  def perform
    validate_mergeable
    merge_conversations
    merge_contact_inboxes
    merge_attributes
    delete_source_contact
  end
end
```

**When to Use**:
- Complex business rules
- Multi-model operations
- External API calls

#### 3. Finders

**Purpose**: Complex queries with filtering

```ruby
# app/finders/conversation_finder.rb
class ConversationFinder
  def perform
    conversations = base_query
    conversations = apply_filters(conversations)
    conversations = apply_sorting(conversations)
    conversations = paginate(conversations)
    
    { conversations: conversations, count: count }
  end
end
```

**When to Use**:
- Complex filtering logic
- Multiple query conditions
- Reusable query patterns

#### 4. Dispatchers

**Purpose**: Event distribution

```ruby
# app/dispatchers/async_dispatcher.rb
class AsyncDispatcher
  def dispatch(event, data)
    broadcast_to_clients(event, data)
    trigger_webhooks(event, data)
    update_analytics(event, data)
  end
end
```

**When to Use**:
- Event broadcasting
- Webhook triggering
- Notification distribution

---

## Database Schema & Relationships

### Primary Relationships

```ruby
# One-to-Many
Account has_many :inboxes
Account has_many :conversations
Account has_many :contacts
Inbox has_many :conversations
Contact has_many :conversations
Conversation has_many :messages

# Many-to-Many (through join table)
Account has_many :users, through: :account_users
Inbox has_many :members, through: :inbox_members
Conversation has_many :labels, through: :conversation_labels

# Polymorphic
Inbox belongs_to :channel, polymorphic: true
Message belongs_to :sender, polymorphic: true
```

### JSONB Usage

Chatwoot heavily uses PostgreSQL JSONB for flexible attributes:

```ruby
# Account settings
account.settings = {
  auto_resolve_after: 40,
  timezone: 'America/New_York',
  working_hours: {
    monday: { enabled: true, from: '09:00', to: '17:00' }
  }
}

# Contact custom attributes
contact.custom_attributes = {
  company: 'Acme Corp',
  plan: 'enterprise',
  ltv: 50000,
  tags: ['vip', 'technical']
}

# Message content attributes
message.content_attributes = {
  items: [
    { title: 'Product A', price: 29.99 },
    { title: 'Product B', price: 49.99 }
  ],
  total: 79.98
}
```

### Indexes Strategy

```ruby
# Compound indexes for common queries
add_index :conversations, [:account_id, :inbox_id, :status, :assignee_id]
add_index :messages, [:conversation_id, :account_id, :message_type, :created_at]

# Unique indexes for business constraints
add_index :conversations, [:account_id, :display_id], unique: true
add_index :contact_inboxes, [:inbox_id, :source_id], unique: true

# JSONB indexes for filtering
add_index :messages, "(additional_attributes -> 'campaign_id')", using: :gin
```

---

## API Structure

### REST API Organization

```
/api/v1
├── /accounts
│   ├── POST /                    # Create account
│   ├── GET /:id                  # Get account
│   ├── PUT /:id                  # Update account
│   └── /accounts/:account_id
│       ├── /conversations
│       │   ├── GET /             # List conversations
│       │   ├── POST /            # Create conversation
│       │   ├── GET /:id          # Get conversation
│       │   ├── PUT /:id          # Update conversation
│       │   ├── POST /:id/toggle_status
│       │   ├── POST /:id/mute
│       │   └── /conversations/:conversation_id
│       │       └── /messages
│       │           ├── GET /     # List messages
│       │           ├── POST /    # Create message
│       │           └── PUT /:id  # Update message
│       ├── /contacts
│       │   ├── GET /             # List contacts
│       │   ├── POST /            # Create contact
│       │   ├── GET /:id          # Get contact
│       │   └── PUT /:id          # Update contact
│       ├── /inboxes
│       │   ├── GET /             # List inboxes
│       │   ├── POST /            # Create inbox
│       │   ├── GET /:id          # Get inbox
│       │   └── PUT /:id          # Update inbox
│       ├── /agents
│       │   ├── GET /             # List agents
│       │   ├── POST /            # Add agent
│       │   └── DELETE /:id       # Remove agent
│       └── /teams
│           ├── GET /             # List teams
│           ├── POST /            # Create team
│           └── GET /:id          # Get team
```

### Request/Response Patterns

**Request Headers:**
```
Content-Type: application/json
api_access_token: <token>
```

**Standard Response Format:**
```json
{
  "id": 123,
  "attribute": "value",
  "created_at": "2024-01-01T00:00:00.000Z",
  "updated_at": "2024-01-01T00:00:00.000Z"
}
```

**Error Response:**
```json
{
  "error": "Error message",
  "message": "Detailed error description"
}
```

**Paginated Response:**
```json
{
  "data": [...],
  "meta": {
    "count": 100,
    "current_page": 1,
    "per_page": 25
  }
}
```

---

## Background Jobs & Async Processing

### Sidekiq Jobs

```ruby
# app/jobs/send_email_notification_job.rb
class SendEmailNotificationJob < ApplicationJob
  queue_as :medium
  
  def perform(message_id)
    message = Message.find_by(id: message_id)
    return unless message
    
    # Send email to assigned agent
    if message.conversation.assignee
      ConversationMailer
        .new_message(message)
        .deliver_now
    end
  rescue StandardError => e
    Rails.logger.error "Email notification failed: #{e.message}"
    # Could retry or send to error tracking
  end
end

# Usage
SendEmailNotificationJob.perform_later(message.id)
```

### Job Priorities

```ruby
# config/sidekiq.yml
:queues:
  - [critical, 4]   # Webhooks, real-time events
  - [high, 3]       # User-facing operations
  - [medium, 2]     # Notifications, emails
  - [low, 1]        # Analytics, cleanup
  - [scheduled, 1]  # Periodic tasks
```

### Scheduled Jobs

```ruby
# config/schedule.rb (whenever gem)
every 1.hour do
  runner "Conversations::ResolveStaleJob.perform_later"
end

every 1.day, at: '2:00 am' do
  runner "Analytics::DailyReportJob.perform_later"
end
```

---

## WebSocket & Real-time Communication

### ActionCable Channels

```ruby
# app/channels/conversation_channel.rb
class ConversationChannel < ApplicationCable::Channel
  def subscribed
    conversation = Conversation.find(params[:conversation_id])
    
    # Authorize
    return reject unless current_user.can_access?(conversation)
    
    # Subscribe to conversation updates
    stream_from "conversation:#{conversation.id}"
  end
  
  def typing
    # Broadcast typing indicator
    ActionCable.server.broadcast(
      "conversation:#{params[:conversation_id]}",
      {
        event: 'user.typing',
        user: current_user.as_json
      }
    )
  end
end
```

**Client-side (Vue):**
```javascript
// Subscribe to conversation
const cable = ActionCable.createConsumer()
const subscription = cable.subscriptions.create(
  { 
    channel: 'ConversationChannel',
    conversation_id: 123
  },
  {
    received(data) {
      if (data.event === 'message.created') {
        // Add message to UI
      }
    }
  }
)

// Send typing indicator
subscription.perform('typing')
```

### Broadcasting Pattern

```ruby
# Broadcast message creation
ActionCable.server.broadcast(
  "conversation:#{conversation.id}",
  {
    event: 'message.created',
    data: message.as_json(include: :sender)
  }
)

# Broadcast conversation update
ActionCable.server.broadcast(
  "account:#{account.id}",
  {
    event: 'conversation.updated',
    data: conversation.as_json
  }
)
```

---

## Authentication & Authorization

### Authentication (Devise Token Auth)

```ruby
# User signs in
POST /auth/sign_in
{
  "email": "user@example.com",
  "password": "password"
}

# Response includes headers:
# access-token: <token>
# client: <client-id>
# uid: user@example.com

# Subsequent requests include these headers
GET /api/v1/accounts/1/conversations
Headers:
  access-token: <token>
  client: <client-id>
  uid: user@example.com
```

### Authorization (Pundit)

```ruby
# app/policies/conversation_policy.rb
class ConversationPolicy < ApplicationPolicy
  def show?
    # Can view if:
    # 1. Assigned to user
    # 2. User is admin
    # 3. Part of assigned team
    record.assignee_id == user.id ||
      user.administrator? ||
      user.teams.include?(record.team)
  end
  
  def update?
    show? && user.agent?
  end
  
  def create_message?
    show?
  end
end

# Usage in controller
authorize @conversation, :update?
```

### Multi-tenancy

```ruby
# Current context (thread-safe)
class Current < ActiveSupport::CurrentAttributes
  attribute :user, :account
end

# Set in controller
class Api::BaseController < ApplicationController
  before_action :set_current_user
  before_action :set_current_account
  
  private
  
  def set_current_user
    Current.user = current_user
  end
  
  def set_current_account
    Current.account = current_user.accounts.find(params[:account_id])
  end
end

# Use anywhere
Current.account.conversations.create(...)
```

---

## Implementation Examples

### Complete Feature: Auto-Assignment

**1. Configuration (Model)**
```ruby
# app/models/inbox.rb
class Inbox < ApplicationRecord
  # Settings
  # { enable_auto_assignment: true, round_robin: true }
  
  def enable_auto_assignment?
    settings['enable_auto_assignment'] == true
  end
end
```

**2. Service (Business Logic)**
```ruby
# app/services/auto_assignment/assignment_service.rb
class AutoAssignment::AssignmentService
  def initialize(conversation:)
    @conversation = conversation
    @inbox = conversation.inbox
  end
  
  def perform
    return unless should_auto_assign?
    
    agent = select_agent
    assign_conversation(agent) if agent
  end
  
  private
  
  def should_auto_assign?
    @inbox.enable_auto_assignment? &&
      @conversation.unassigned? &&
      available_agents.any?
  end
  
  def select_agent
    RoundRobinSelector.new(
      inbox: @inbox,
      allowed_agent_ids: available_agents.pluck(:id)
    ).perform
  end
  
  def available_agents
    @inbox.members
           .where(availability: :online)
           .where.not(id: unavailable_agent_ids)
  end
  
  def assign_conversation(agent)
    @conversation.update!(
      assignee: agent,
      status: :open
    )
    
    # Notify agent
    SendAssignmentNotificationJob.perform_later(
      @conversation.id,
      agent.id
    )
  end
end
```

**3. Selector (Algorithm)**
```ruby
# app/services/auto_assignment/round_robin_selector.rb
class AutoAssignment::RoundRobinSelector
  def initialize(inbox:, allowed_agent_ids:)
    @inbox = inbox
    @allowed_agent_ids = allowed_agent_ids
  end
  
  def perform
    return nil if @allowed_agent_ids.empty?
    
    # Find agent with least active conversations
    agent_loads = calculate_agent_loads
    agent_loads.min_by { |_, count| count }&.first
  end
  
  private
  
  def calculate_agent_loads
    User.where(id: @allowed_agent_ids).map do |agent|
      active_count = agent.assigned_conversations
                          .where(inbox_id: @inbox.id)
                          .where(status: [:open, :pending])
                          .count
      
      [agent, active_count]
    end.to_h
  end
end
```

**4. Trigger (Callback)**
```ruby
# app/models/conversation.rb
class Conversation < ApplicationRecord
  after_create :assign_agent, if: :should_auto_assign?
  
  private
  
  def should_auto_assign?
    inbox.enable_auto_assignment? && unassigned?
  end
  
  def assign_agent
    AutoAssignment::AssignmentService.new(
      conversation: self
    ).perform
  end
end
```

---

## Laravel Equivalent Examples

### 1. Model Relationships (Laravel)

**Rails:**
```ruby
class Conversation < ApplicationRecord
  belongs_to :account
  belongs_to :inbox
  has_many :messages
  belongs_to :assignee, class_name: 'User', optional: true
end
```

**Laravel:**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    
    public function inbox()
    {
        return $this->belongsTo(Inbox::class);
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    
    protected $casts = [
        'custom_attributes' => 'array',
        'additional_attributes' => 'array',
        'last_activity_at' => 'datetime',
    ];
}
```

### 2. Controller (Laravel)

**Rails:**
```ruby
class Api::V1::Accounts::ConversationsController < ApplicationController
  def create
    @conversation = ConversationBuilder.new(
      params: params,
      contact_inbox: @contact_inbox
    ).perform
    
    render json: @conversation, status: :created
  end
end
```

**Laravel:**
```php
<?php
namespace App\Http\Controllers\Api\V1\Accounts;

use App\Services\ConversationBuilder;
use Illuminate\Http\Request;

class ConversationsController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'inbox_id' => 'required|exists:inboxes,id',
            'contact_id' => 'required|exists:contacts,id',
            'message' => 'string|nullable'
        ]);
        
        $conversation = app(ConversationBuilder::class)
            ->setParams($validated)
            ->setContactInbox($contactInbox)
            ->build();
        
        return response()->json($conversation, 201);
    }
}
```

### 3. Service Object (Laravel)

**Rails:**
```ruby
class Messages::MessageBuilder
  def initialize(user, conversation, params)
    @user = user
    @conversation = conversation
    @params = params
  end
  
  def perform
    create_message
    dispatch_events
    @message
  end
end
```

**Laravel:**
```php
<?php
namespace App\Services\Messages;

use App\Models\Message;
use App\Events\MessageCreated;
use Illuminate\Support\Facades\DB;

class MessageBuilder
{
    private $user;
    private $conversation;
    private $params;
    
    public function __construct($user, $conversation, array $params)
    {
        $this->user = $user;
        $this->conversation = $conversation;
        $this->params = $params;
    }
    
    public function build(): Message
    {
        return DB::transaction(function () {
            $message = $this->createMessage();
            $this->updateConversation();
            $this->dispatchEvents($message);
            
            return $message;
        });
    }
    
    private function createMessage(): Message
    {
        return $this->conversation->messages()->create([
            'account_id' => $this->conversation->account_id,
            'inbox_id' => $this->conversation->inbox_id,
            'sender_id' => $this->user->id,
            'sender_type' => get_class($this->user),
            'content' => $this->params['content'],
            'message_type' => $this->params['message_type'] ?? 'outgoing',
        ]);
    }
    
    private function dispatchEvents(Message $message): void
    {
        event(new MessageCreated($message));
    }
}
```

### 4. Background Jobs (Laravel)

**Rails:**
```ruby
class SendEmailNotificationJob < ApplicationJob
  def perform(message_id)
    message = Message.find(message_id)
    ConversationMailer.new_message(message).deliver_now
  end
end

# Usage
SendEmailNotificationJob.perform_later(message.id)
```

**Laravel:**
```php
<?php
namespace App\Jobs;

use App\Models\Message;
use App\Mail\NewMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    private $messageId;
    
    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }
    
    public function handle(): void
    {
        $message = Message::find($this->messageId);
        
        if ($message && $message->conversation->assignee) {
            Mail::to($message->conversation->assignee)
                ->send(new NewMessage($message));
        }
    }
}

// Usage
SendEmailNotification::dispatch($message->id);
```

### 5. Real-time Broadcasting (Laravel)

**Rails:**
```ruby
ActionCable.server.broadcast(
  "conversation:#{conversation.id}",
  { event: 'message.created', data: message }
)
```

**Laravel:**
```php
<?php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $message;
    
    public function __construct(Message $message)
    {
        $this->message = $message;
    }
    
    public function broadcastOn(): Channel
    {
        return new Channel('conversation.' . $this->message->conversation_id);
    }
    
    public function broadcastAs(): string
    {
        return 'message.created';
    }
    
    public function broadcastWith(): array
    {
        return [
            'data' => $this->message->toArray()
        ];
    }
}

// Usage
event(new MessageCreated($message));
```

### 6. API Routes (Laravel)

**Rails:**
```ruby
namespace :api do
  namespace :v1 do
    resources :accounts do
      resources :conversations do
        resources :messages
      end
    end
  end
end
```

**Laravel:**
```php
<?php
// routes/api.php
use App\Http\Controllers\Api\V1;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::prefix('accounts/{account}')->group(function () {
            Route::apiResource('conversations', V1\ConversationsController::class);
            
            Route::prefix('conversations/{conversation}')->group(function () {
                Route::apiResource('messages', V1\MessagesController::class);
            });
        });
    });
});
```

---

## Replicating in Other Stacks

### Node.js/Express + TypeScript

**Model (TypeORM):**
```typescript
import { Entity, PrimaryGeneratedColumn, Column, ManyToOne, OneToMany } from 'typeorm';

@Entity('conversations')
export class Conversation {
  @PrimaryGeneratedColumn()
  id: number;

  @Column()
  accountId: number;

  @ManyToOne(() => Account, account => account.conversations)
  account: Account;

  @Column()
  inboxId: number;

  @ManyToOne(() => Inbox, inbox => inbox.conversations)
  inbox: Inbox;

  @OneToMany(() => Message, message => message.conversation)
  messages: Message[];

  @Column({ nullable: true })
  assigneeId?: number;

  @ManyToOne(() => User, { nullable: true })
  assignee?: User;

  @Column({ type: 'enum', enum: ['open', 'resolved', 'pending', 'snoozed'] })
  status: string;

  @Column({ type: 'jsonb', nullable: true })
  customAttributes?: Record<string, any>;

  @Column({ type: 'timestamp' })
  lastActivityAt: Date;
}
```

**Service:**
```typescript
// services/MessageBuilder.ts
import { Injectable } from '@nestjs/common';
import { Message } from '../entities/Message';
import { Conversation } from '../entities/Conversation';
import { User } from '../entities/User';

@Injectable()
export class MessageBuilder {
  constructor(
    private readonly messageRepository: Repository<Message>,
    private readonly eventEmitter: EventEmitter2
  ) {}

  async build(
    user: User,
    conversation: Conversation,
    params: CreateMessageDto
  ): Promise<Message> {
    return await this.messageRepository.manager.transaction(async (manager) => {
      // Create message
      const message = manager.create(Message, {
        accountId: conversation.accountId,
        conversationId: conversation.id,
        inboxId: conversation.inboxId,
        senderId: user.id,
        senderType: 'User',
        content: params.content,
        messageType: params.messageType || 'outgoing',
      });

      await manager.save(message);

      // Update conversation
      await manager.update(Conversation, conversation.id, {
        lastActivityAt: new Date(),
      });

      // Emit event
      this.eventEmitter.emit('message.created', { message });

      return message;
    });
  }
}
```

**Controller:**
```typescript
// controllers/messages.controller.ts
import { Controller, Post, Body, Param, UseGuards } from '@nestjs/common';
import { MessageBuilder } from '../services/MessageBuilder';
import { CurrentUser } from '../decorators/current-user';

@Controller('api/v1/accounts/:accountId/conversations/:conversationId/messages')
@UseGuards(AuthGuard)
export class MessagesController {
  constructor(private readonly messageBuilder: MessageBuilder) {}

  @Post()
  async create(
    @CurrentUser() user: User,
    @Param('conversationId') conversationId: number,
    @Body() createMessageDto: CreateMessageDto
  ) {
    const conversation = await this.conversationRepository.findOneOrFail(conversationId);
    
    const message = await this.messageBuilder.build(
      user,
      conversation,
      createMessageDto
    );

    return { data: message };
  }
}
```

### Python/Django

**Model:**
```python
# models.py
from django.db import models
from django.contrib.postgres.fields import JSONField

class Conversation(models.Model):
    class Status(models.TextChoices):
        OPEN = 'open', 'Open'
        RESOLVED = 'resolved', 'Resolved'
        PENDING = 'pending', 'Pending'
        SNOOZED = 'snoozed', 'Snoozed'

    account = models.ForeignKey('Account', on_delete=models.CASCADE, related_name='conversations')
    inbox = models.ForeignKey('Inbox', on_delete=models.CASCADE, related_name='conversations')
    contact = models.ForeignKey('Contact', on_delete=models.CASCADE, null=True, related_name='conversations')
    assignee = models.ForeignKey('User', on_delete=models.SET_NULL, null=True, related_name='assigned_conversations')
    
    status = models.CharField(max_length=20, choices=Status.choices, default=Status.OPEN)
    display_id = models.IntegerField()
    custom_attributes = models.JSONField(null=True, blank=True)
    last_activity_at = models.DateTimeField(auto_now=True)
    
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'conversations'
        unique_together = ['account', 'display_id']
        indexes = [
            models.Index(fields=['account', 'inbox', 'status', 'assignee']),
        ]
```

**Service:**
```python
# services/message_builder.py
from django.db import transaction
from channels.layers import get_channel_layer
from asgiref.sync import async_to_sync

class MessageBuilder:
    def __init__(self, user, conversation, params):
        self.user = user
        self.conversation = conversation
        self.params = params

    def build(self):
        with transaction.atomic():
            message = self._create_message()
            self._update_conversation()
            self._dispatch_events(message)
            return message

    def _create_message(self):
        return Message.objects.create(
            account_id=self.conversation.account_id,
            conversation=self.conversation,
            inbox_id=self.conversation.inbox_id,
            sender=self.user,
            content=self.params.get('content'),
            message_type=self.params.get('message_type', 'outgoing')
        )

    def _update_conversation(self):
        self.conversation.last_activity_at = timezone.now()
        self.conversation.save(update_fields=['last_activity_at'])

    def _dispatch_events(self, message):
        # Broadcast via Channels
        channel_layer = get_channel_layer()
        async_to_sync(channel_layer.group_send)(
            f'conversation_{self.conversation.id}',
            {
                'type': 'message_created',
                'message': message.to_dict()
            }
        )
```

**View:**
```python
# views/messages.py
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from services.message_builder import MessageBuilder

class MessageCreateView(APIView):
    def post(self, request, account_id, conversation_id):
        conversation = Conversation.objects.get(
            id=conversation_id,
            account_id=account_id
        )
        
        builder = MessageBuilder(
            user=request.user,
            conversation=conversation,
            params=request.data
        )
        
        message = builder.build()
        
        return Response(
            MessageSerializer(message).data,
            status=status.HTTP_201_CREATED
        )
```

---

## Summary

### Key Takeaways

1. **Multi-tenant Architecture**: Everything scoped by Account
2. **Service Layer**: Business logic separated from controllers
3. **Builder Pattern**: Complex object creation
4. **JSONB Flexibility**: Dynamic attributes without schema changes
5. **Real-time First**: WebSocket for all updates
6. **Async Processing**: Background jobs for heavy operations
7. **Polymorphic Associations**: Flexible relationships (channels, senders)

### Replication Checklist

To replicate Chatwoot's backend in any stack:

- [ ] Multi-tenant data model (Account as root)
- [ ] Polymorphic associations (Channel, Sender)
- [ ] Service layer for business logic
- [ ] Background job processing
- [ ] Real-time WebSocket communication
- [ ] Token-based API authentication
- [ ] Role-based authorization
- [ ] JSONB/JSON fields for flexibility
- [ ] Comprehensive indexing strategy
- [ ] Event-driven architecture

---

**Version:** 1.0.0  
**Last Updated:** December 2024  
**Chatwoot Version:** 4.9.1

For questions or clarifications, please refer to:
- [Chatwoot Repository](https://github.com/chatwoot/chatwoot)
- [Official Documentation](https://www.chatwoot.com/help-center)
