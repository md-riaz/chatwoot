<script lang="ts">
  /**
   * WhatsAppTemplate Component
   * Wrapper for WhatsAppTemplateParser with styled container
   * Used in NewConversation flow
   */
  import WhatsAppTemplateParser from './WhatsAppTemplateParser.svelte';
  import { Button } from '$lib/components/ui/button';
  import type { WhatsAppTemplate } from '$lib/helpers/templateHelper';

  interface Props {
    template: WhatsAppTemplate;
    onsendMessage?: (payload: any) => void;
    onback?: () => void;
  }

  let { template, onsendMessage, onback }: Props = $props();

  function handleSendMessage(payload: any) {
    onsendMessage?.(payload);
  }

  function handleBack() {
    onback?.();
  }
</script>

<div class="absolute top-full mt-1.5 max-h-[30rem] overflow-y-auto left-0 flex flex-col gap-4 px-4 pt-6 pb-5 items-start w-[28.75rem] h-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-lg rounded-lg z-50">
  <div class="w-full">
    <WhatsAppTemplateParser
      {template}
      onsendMessage={handleSendMessage}
      onback={handleBack}
    >
      {#snippet children({ sendMessage, goBack, disabled })}
        <div class="flex gap-3 justify-between items-end w-full h-14">
          <Button
            variant="outline"
            class="w-full font-medium"
            onclick={goBack}
          >
            Back
          </Button>
          <Button
            class="w-full font-medium"
            {disabled}
            onclick={sendMessage}
          >
            Send Message
          </Button>
        </div>
      {/snippet}
    </WhatsAppTemplateParser>
  </div>
</div>
