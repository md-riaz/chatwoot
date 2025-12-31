<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { base } from '$app/paths';

  const components = [
    'accordion', 'alert', 'article-card', 'assignment-policy', 'assistant-card', 
    'audio-recorder', 'availability', 'avatar', 'badge', 'breadcrumb', 'button', 
    'calendar', 'captain', 'card', 'category-card', 'changelog-card', 'checkbox', 
    'combobox', 'command', 'confirm-button', 'contact-card', 'contact-form', 
    'contact-header', 'contact-management', 'contact-note', 'conversation-card', 
    'copilot', 'copilot-loader', 'custom-attributes', 'date-picker', 'dialog', 
    'document-card', 'dropdown-menu', 'emoji-picker', 'empty-state', 
    'feature-spotlight', 'file-icon', 'file-upload', 'filter', 'flag', 'form', 
    'help-center', 'image-gallery', 'inline-input', 'input', 'label', 'label-input', 
    'locale-card', 'message-bubble', 'message-template', 'new-conversation', 
    'pagination', 'phone-input', 'popover', 'portal-switcher', 'progress', 
    'radio-group', 'reply-box', 'scroll-area', 'select', 'select-menu', 'separator', 
    'sheet', 'sidebar', 'sidebar-actions', 'skeleton', 'spinner', 'switch', 
    'tab-bar', 'table', 'tabs', 'tag-input', 'textarea', 'toast', 'tooltip'
  ];

  let searchQuery = '';
  
  $: filteredComponents = components.filter(c => 
    c.toLowerCase().includes(searchQuery.toLowerCase())
  );
</script>

<div class="container mx-auto py-10">
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
      <h1 class="text-4xl font-bold mb-2">Chatwoot Svelte UI</h1>
      <p class="text-muted-foreground">
        A modern UI component library built with SvelteKit, shadcn-svelte, and Tailwind CSS.
      </p>
    </div>
    <div class="w-full md:w-auto">
      <Button href="http://pnpm.io" variant="outline" target="_blank">Documentation</Button>
    </div>
  </div>
  
  <div class="flex gap-4 mb-8">
    <div class="relative w-full max-w-sm">
      <Input type="search" placeholder="Search components..." bind:value={searchQuery} />
    </div>
  </div>
  
  <div class="mt-6">
    <h2 class="text-2xl font-semibold mb-4">Available Components ({filteredComponents.length})</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
      {#each filteredComponents as component}
        <a href="{base}/ui/{component}" class="block p-4 border rounded-lg hover:border-primary transition-colors hover:bg-muted/50">
          <h3 class="font-medium capitalize mb-1">{component.replace(/-/g, ' ')}</h3>
          <p class="text-xs text-muted-foreground">Component</p>
        </a>
      {/each}
    </div>
    
    {#if filteredComponents.length === 0}
      <div class="text-center py-10 text-muted-foreground">
        No components found matching "{searchQuery}"
      </div>
    {/if}
  </div>
  
  <div class="mt-16 pt-8 border-t">
    <h2 class="text-2xl font-semibold mb-4">Quick Start</h2>
    <div class="grid md:grid-cols-2 gap-6">
      <div>
        <h3 class="font-medium mb-2">Development</h3>
        <pre class="bg-muted p-4 rounded-lg text-sm overflow-x-auto"><code>pnpm dev</code></pre>
      </div>
      <div>
        <h3 class="font-medium mb-2">Storybook / Histoire</h3>
        <pre class="bg-muted p-4 rounded-lg text-sm overflow-x-auto"><code>pnpm story:dev</code></pre>
      </div>
    </div>
  </div>
</div>
