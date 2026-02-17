<script lang="ts">
  /**
   * MessageComposer - Main message composition component
   * Features: Rich text input, file attachments, emoji picker, private notes
   */

  import { onMount } from 'svelte';
  import { Send, Paperclip, Smile, AtSign } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { Textarea } from '$lib/components/ui/textarea';
  import * as ReplyBox from '$lib/components/ui/reply-box';
  import * as EmojiPicker from '$lib/components/ui/emoji-picker';
  import * as FileUpload from '$lib/components/ui/file-upload';
  import { Switch } from '$lib/components/ui/switch';
  import { Label } from '$lib/components/ui/label';
  import { Badge } from '$lib/components/ui/badge';
  import ReplyTopPanel from './ReplyTopPanel.svelte';

  interface Props {
    conversationId: number;
    mode?: MessageMode;
    onSend?: (
      content: string,
      attachments: File[],
      isPrivate: boolean
    ) => Promise<void>;
  }

  let { conversationId, mode = 'reply', onSend }: Props = $props();

  // Local state
  let messageContent = $state('');
  let attachments = $state<Attachment[]>([]);
  let isPrivate = $state(false);
  let isSending = $state(false);
  let showEmojiPicker = $state(false);
  let showFileUpload = $state(false);
  let textareaElement = $state<HTMLTextAreaElement | null>(null);

  // Derived values
  const isSendDisabled = $derived(
    !messageContent.trim() && attachments.length === 0
  );
  const characterCount = $derived(messageContent.length);
  const hasContent = $derived(
    messageContent.trim().length > 0 || attachments.length > 0
  );

  // Load draft from localStorage
  function loadDraft() {
    try {
      const draftKey = `message_draft_${conversationId}`;
      const saved = localStorage.getItem(draftKey);
      if (saved) {
        const draft = JSON.parse(saved);
        messageContent = draft.content || '';
        isPrivate = draft.isPrivate || false;
      }
    } catch (error) {
      console.error('Failed to load draft:', error);
    }
  }

  // Save draft to localStorage
  function saveDraft() {
    try {
      const draftKey = `message_draft_${conversationId}`;
      const draft = {
        content: messageContent,
        isPrivate,
        timestamp: Date.now(),
      };
      localStorage.setItem(draftKey, JSON.stringify(draft));
    } catch (error) {
      console.error('Failed to save draft:', error);
    }
  }

  // Clear draft
  function clearDraft() {
    try {
      const draftKey = `message_draft_${conversationId}`;
      localStorage.removeItem(draftKey);
    } catch (error) {
      console.error('Failed to clear draft:', error);
    }
  }

  // Handle file selection
  function handleFileSelect(files: File[]) {
    const newAttachments: Attachment[] = files.map(file => ({
      file,
      name: file.name,
      size: file.size,
      type: file.type,
      preview: file.type.startsWith('image/')
        ? URL.createObjectURL(file)
        : undefined,
    }));
    attachments = [...attachments, ...newAttachments];
    showFileUpload = false;
  }

  // Remove attachment
  function removeAttachment(index: number) {
    const attachment = attachments[index];
    if (attachment.preview) {
      URL.revokeObjectURL(attachment.preview);
    }
    attachments = attachments.filter((_, i) => i !== index);
  }

  // Handle emoji selection
  function handleEmojiSelect(emoji: string) {
    const cursorPos = textareaElement?.selectionStart || messageContent.length;
    messageContent =
      messageContent.slice(0, cursorPos) +
      emoji +
      messageContent.slice(cursorPos);
    showEmojiPicker = false;
    textareaElement?.focus();
  }

  // Handle send message
  async function handleSend() {
    if (isSendDisabled || isSending) return;

    isSending = true;
    try {
      const files = attachments.map(a => a.file);

      if (onSend) {
        await onSend(messageContent, files, isPrivate);
      } else {
        // Default: use messagesStore
        await messagesStore.sendMessage({
          conversationId,
          message: messageContent,
          private: isPrivate,
          files: files,
        });
      }

      // Clear form on success
      messageContent = '';
      attachments = [];
      clearDraft();
    } catch (error) {
      console.error('Failed to send message:', error);
    } finally {
      isSending = false;
    }
  }

  // Handle keyboard shortcuts
  function handleKeyDown(e: KeyboardEvent) {
    // Ctrl/Cmd + Enter to send
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      handleSend();
    }
    // Alt + P for Private Note
    if (e.altKey && e.code === 'KeyP') {
      e.preventDefault();
      handleModeChange('private');
    }
    // Alt + L for Reply (Vue parity uses L for Reply? Wait, Vue uses P for Private, L for Reply?)
    // Checking ReplyTopPanel.vue: Alt+KeyP (Note), Alt+KeyL (Reply).
    if (e.altKey && e.code === 'KeyL') {
      e.preventDefault();
      handleModeChange('reply');
    }
  }

  function handleModeChange(newMode: 'reply' | 'private') {
    isPrivate = newMode === 'private';
  }

  // Auto-save draft
  let draftTimeout: ReturnType<typeof setTimeout>;
  $effect(() => {
    if (messageContent || isPrivate) {
      clearTimeout(draftTimeout);
      draftTimeout = setTimeout(() => {
        saveDraft();
      }, 1000);
    }
    // Update textarea background when mode changes
    if (textareaElement) {
      // We can't easily set class directly on the mounted component's internal element without props
      // But we can rely on the container class.
    }
  });

  // Load draft on mount
  onMount(() => {
    loadDraft();

    return () => {
      // Cleanup attachment previews
      attachments.forEach(attachment => {
        if (attachment.preview) {
          URL.revokeObjectURL(attachment.preview);
        }
      });
    };
  });
</script>

<div
  class={`rounded-xl border shadow-sm flex flex-col transition-colors ${isPrivate ? 'bg-amber-50/50 border-amber-200' : 'bg-background border-border'}`}
>
  <!-- Top Actions Bar -->
  <div class="flex items-center justify-between p-2 pb-0">
    <ReplyTopPanel
      mode={isPrivate ? 'private' : 'reply'}
      onModeChange={handleModeChange}
    />
    {#if characterCount > 0}
      <Badge variant="outline" class="text-xs ml-auto">
        {characterCount} chars
      </Badge>
    {/if}
  </div>

  <!-- Attachments Preview -->
  {#if attachments.length > 0}
    <div
      class="mx-3 mt-3 p-2 bg-background/50 rounded-md border border-border/50"
    >
      <div class="flex flex-wrap gap-2">
        {#each attachments as attachment, index}
          <div class="relative group">
            {#if attachment.preview}
              <!-- Image preview -->
              <div class="relative w-20 h-20 rounded-md overflow-hidden border">
                <img
                  src={attachment.preview}
                  alt={attachment.name}
                  class="w-full h-full object-cover"
                />
                <button
                  class="absolute top-1 right-1 bg-destructive text-destructive-foreground rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                  onclick={() => removeAttachment(index)}
                  aria-label="Remove attachment"
                >
                  <span class="text-xs">×</span>
                </button>
              </div>
            {:else}
              <!-- File preview -->
              <div
                class="flex items-center gap-2 p-2 bg-background rounded-md border min-w-[160px]"
              >
                <Paperclip class="h-4 w-4 text-muted-foreground" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm truncate">{attachment.name}</p>
                  <p class="text-xs text-muted-foreground">
                    {(attachment.size / 1024).toFixed(1)} KB
                  </p>
                </div>
                <button
                  class="text-destructive hover:text-destructive/80"
                  onclick={() => removeAttachment(index)}
                  aria-label="Remove attachment"
                >
                  <span class="text-sm">×</span>
                </button>
              </div>
            {/if}
          </div>
        {/each}
      </div>
    </div>
  {/if}

  <!-- Message Input -->
  <div class="px-3 py-2">
    <Textarea
      bind:value={messageContent}
      bind:ref={textareaElement}
      placeholder={isPrivate ? 'Add a private note...' : 'Type your message...'}
      class={`min-h-[120px] resize-none border-0 focus-visible:ring-0 shadow-none bg-transparent ${isPrivate ? 'placeholder:text-amber-900/40 text-amber-900' : ''}`}
      disabled={isSending}
      onkeydown={handleKeyDown}
    />
  </div>

  <!-- Bottom Actions Bar -->
  <div class="flex items-center justify-between p-2 pt-0">
    <div class="flex items-center gap-1">
      <!-- Emoji Picker Button -->
      <Button
        variant="ghost"
        size="icon"
        onclick={() => (showEmojiPicker = !showEmojiPicker)}
        title="Add emoji"
        class={isPrivate
          ? 'text-amber-900 hover:text-amber-950 hover:bg-amber-100'
          : ''}
      >
        <Smile class="h-4 w-4" />
      </Button>

      <!-- File Upload Button -->
      <Button
        variant="ghost"
        size="icon"
        onclick={() => (showFileUpload = !showFileUpload)}
        title="Attach file"
        class={isPrivate
          ? 'text-amber-900 hover:text-amber-950 hover:bg-amber-100'
          : ''}
      >
        <Paperclip class="h-4 w-4" />
      </Button>

      <!-- Mention Button (placeholder) -->
      <Button
        variant="ghost"
        size="icon"
        title="Mention"
        disabled
        class={isPrivate
          ? 'text-amber-900 hover:text-amber-950 hover:bg-amber-100'
          : ''}
      >
        <AtSign class="h-4 w-4" />
      </Button>
    </div>

    <!-- Send Button -->
    <Button
      onclick={handleSend}
      disabled={isSendDisabled || isSending}
      class={`gap-2 min-w-[100px] ${isPrivate ? 'bg-amber-400 text-amber-950 hover:bg-amber-500' : ''}`}
    >
      <Send class="h-4 w-4" />
      {#if isSending}
        {isPrivate ? 'Creating...' : 'Sending...'}
      {:else}
        {isPrivate ? 'Create Note' : 'Send'}
      {/if}
    </Button>
  </div>

  <!-- Emoji Picker Popover -->
  {#if showEmojiPicker}
    <div class="absolute bottom-full left-0 mb-2 z-50">
      <div class="bg-popover border rounded-lg shadow-lg">
        <EmojiPicker.Root onEmojiSelect={handleEmojiSelect} class="w-80" />
      </div>
    </div>
  {/if}

  <!-- File Upload Dialog -->
  {#if showFileUpload}
    <div class="absolute bottom-full left-0 mb-2 z-50 w-full max-w-md">
      <div class="bg-popover border rounded-lg shadow-lg p-4">
        <FileUpload.Root
          onFilesSelected={handleFileSelect}
          multiple={true}
          accept="image/*,application/pdf,.doc,.docx"
          maxSize={10 * 1024 * 1024}
        />
        <div class="mt-2 flex justify-end">
          <Button
            variant="ghost"
            size="sm"
            onclick={() => (showFileUpload = false)}
          >
            Cancel
          </Button>
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  :global(.relative) {
    position: relative;
  }
</style>
