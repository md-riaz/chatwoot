<script lang="ts">
  import { widgetConversationStore } from '$lib/widget/stores/conversation.svelte';
  import { widgetConfigStore } from '$lib/widget/stores/config.svelte';
  import { User, Mail, Phone } from 'lucide-svelte';

  interface Props {
    onsubmit?: (data: FormData) => void;
  }

  let { onsubmit }: Props = $props();

  let name = $state('');
  let email = $state('');
  let phone = $state('');
  let message = $state('');
  let errors = $state<Record<string, string>>({});
  let isSubmitting = $state(false);

  const config = $derived(widgetConfigStore.configuration);
  const preChatOptions = $derived(config?.preChatFormOptions);
  const requireName = $derived(preChatOptions?.requireName || false);
  const requireEmail = $derived(preChatOptions?.requireEmail || false);
  const requirePhone = $derived(preChatOptions?.requirePhoneNumber || false);

  function validateForm(): boolean {
    errors = {};

    if (requireName && !name.trim()) {
      errors.name = 'Name is required';
    }

    if (requireEmail && !email.trim()) {
      errors.email = 'Email is required';
    } else if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errors.email = 'Invalid email address';
    }

    if (requirePhone && !phone.trim()) {
      errors.phone = 'Phone number is required';
    } else if (phone && !/^[\d\s\-\+\(\)]+$/.test(phone)) {
      errors.phone = 'Invalid phone number';
    }

    return Object.keys(errors).length === 0;
  }

  async function handleSubmit() {
    if (!validateForm()) return;

    isSubmitting = true;

    try {
      // Create conversation with contact info
      const conversation = await widgetConversationStore.createConversation({
        contact: {
          name: name.trim() || undefined,
          email: email.trim() || undefined,
          phoneNumber: phone.trim() || undefined,
        },
        message: message.trim() || undefined,
      });

      if (conversation && onsubmit) {
        onsubmit(new FormData());
      }
    } catch (err) {
      console.error('Failed to create conversation:', err);
      errors.submit = 'Failed to start conversation. Please try again.';
    } finally {
      isSubmitting = false;
    }
  }
</script>

<div class="pre-chat-form">
  <div class="form-header">
    <h3>Start a conversation</h3>
    <p>Please provide your details to get started</p>
  </div>

  <form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
    <div class="form-group">
      <label for="name" class="form-label">
        <User size={16} />
        Name {requireName ? '*' : ''}
      </label>
      <input
        id="name"
        type="text"
        bind:value={name}
        placeholder="Enter your name"
        class="form-input"
        class:error={errors.name}
        disabled={isSubmitting}
      />
      {#if errors.name}
        <span class="error-message">{errors.name}</span>
      {/if}
    </div>

    <div class="form-group">
      <label for="email" class="form-label">
        <Mail size={16} />
        Email {requireEmail ? '*' : ''}
      </label>
      <input
        id="email"
        type="email"
        bind:value={email}
        placeholder="your@email.com"
        class="form-input"
        class:error={errors.email}
        disabled={isSubmitting}
      />
      {#if errors.email}
        <span class="error-message">{errors.email}</span>
      {/if}
    </div>

    <div class="form-group">
      <label for="phone" class="form-label">
        <Phone size={16} />
        Phone {requirePhone ? '*' : ''}
      </label>
      <input
        id="phone"
        type="tel"
        bind:value={phone}
        placeholder="+1 (555) 123-4567"
        class="form-input"
        class:error={errors.phone}
        disabled={isSubmitting}
      />
      {#if errors.phone}
        <span class="error-message">{errors.phone}</span>
      {/if}
    </div>

    <div class="form-group">
      <label for="message" class="form-label">
        Message (optional)
      </label>
      <textarea
        id="message"
        bind:value={message}
        placeholder="How can we help you?"
        rows="3"
        class="form-textarea"
        disabled={isSubmitting}
      ></textarea>
    </div>

    {#if errors.submit}
      <div class="error-banner">{errors.submit}</div>
    {/if}

    <button type="submit" class="submit-button" disabled={isSubmitting}>
      {isSubmitting ? 'Starting...' : 'Start Conversation'}
    </button>
  </form>
</div>

<style>
  .pre-chat-form {
    padding: 24px;
    max-width: 400px;
    margin: 0 auto;
  }

  .form-header {
    text-align: center;
    margin-bottom: 24px;
  }

  .form-header h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
  }

  .form-header p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
  }

  .form-group {
    margin-bottom: 16px;
  }

  .form-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
  }

  .form-input,
  .form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    transition: border-color 0.2s ease;
  }

  .form-input:focus,
  .form-textarea:focus {
    outline: none;
    border-color: var(--widget-color, #1f93ff);
  }

  .form-input.error {
    border-color: #ef4444;
  }

  .form-input:disabled,
  .form-textarea:disabled {
    background: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
  }

  .form-textarea {
    resize: vertical;
    min-height: 80px;
  }

  .error-message {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #ef4444;
  }

  .error-banner {
    padding: 12px;
    margin-bottom: 16px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 13px;
  }

  .submit-button {
    width: 100%;
    padding: 12px;
    background: var(--widget-color, #1f93ff);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .submit-button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .submit-button:disabled {
    background: #d1d5db;
    cursor: not-allowed;
  }
</style>
