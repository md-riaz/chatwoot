<script lang="ts">
  /**
   * WhatsAppOptions Component
   * Displays WhatsApp templates list with search
   * Used in NewConversation for template selection
   */
  import { Search } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import WhatsAppTemplate from './WhatsAppTemplate.svelte';
  import type { WhatsAppTemplate as WhatsAppTemplateType } from '$lib/helpers/templateHelper';

  interface Props {
    inboxId: number;
    whatsAppTemplates?: WhatsAppTemplateType[];
    onsendMessage?: (payload: any) => void;
  }

  let { inboxId, whatsAppTemplates = [], onsendMessage }: Props = $props();

  let searchQuery = $state('');
  let selectedTemplate = $state<WhatsAppTemplateType | null>(null);
  let showTemplatesMenu = $state(false);

  const filteredTemplates = $derived(
    whatsAppTemplates.filter(template =>
      template.name.toLowerCase().includes(searchQuery.toLowerCase())
    )
  );

  function getTemplateBody(template: WhatsAppTemplateType): string {
    const bodyComponent = template.components.find(component => component.type === 'BODY');
    return bodyComponent?.text || '';
  }

  function handleTriggerClick() {
    searchQuery = '';
    showTemplatesMenu = !showTemplatesMenu;
  }

  function handleTemplateClick(template: WhatsAppTemplateType) {
    selectedTemplate = template;
    showTemplatesMenu = false;
  }

  function handleBack() {
    selectedTemplate = null;
    showTemplatesMenu = true;
  }

  function handleSendMessage(payload: any) {
    onsendMessage?.(payload);
    selectedTemplate = null;
  }
</script>

<div class="relative">
  <Button
    variant="outline"
    size="sm"
    disabled={!!selectedTemplate}
    class="text-xs font-medium"
    onclick={handleTriggerClick}
  >
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4">
      <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"/>
      <path d="M9 10a.5 .5 0 0 0 1 0V9a.5 .5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0-1h-1a.5 .5 0 0 0 0 1"/>
    </svg>
    WhatsApp Templates
  </Button>

  {#if showTemplatesMenu}
    <div class="absolute top-full mt-1.5 max-h-96 overflow-y-auto left-0 flex flex-col gap-2 p-4 items-center w-[21.875rem] h-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-lg rounded-lg z-50">
      <!-- Search Input -->
      <div class="relative w-full">
        <Search class="absolute size-3.5 top-2 left-3 text-slate-400" />
        <Input
          type="search"
          placeholder="Search templates..."
          bind:value={searchQuery}
          class="w-full h-8 py-2 pl-10 pr-2 text-sm"
        />
      </div>

      <!-- Templates List -->
      {#if filteredTemplates.length === 0}
        <div class="flex flex-col items-center justify-center py-8 text-sm text-slate-500">
          <p>No templates found</p>
        </div>
      {:else}
        {#each filteredTemplates as template (template.id)}
          <button
            type="button"
            class="flex flex-col gap-2 p-2 w-full rounded-lg cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 text-left transition-colors"
            onclick={() => handleTemplateClick(template)}
          >
            <div class="font-medium text-sm">{template.name}</div>
            <div class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
              {getTemplateBody(template)}
            </div>
          </button>
        {/each}
      {/if}
    </div>
  {/if}

  {#if selectedTemplate}
    <WhatsAppTemplate
      template={selectedTemplate}
      onsendMessage={handleSendMessage}
      onback={handleBack}
    />
  {/if}
</div>
