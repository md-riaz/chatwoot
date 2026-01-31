<script lang="ts">
  /**
   * Import Contacts Dialog
   * Upload CSV file to import contacts
   */
  import { createEventDispatcher } from 'svelte';
  import {
    Upload,
    FileSpreadsheet,
    AlertCircle,
    CheckCircle,
  } from 'lucide-svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { importContacts } from '$lib/api/contacts';
  import { page } from '$app/stores';

  // Props
  interface Props {
    open?: boolean;
  }

  let { open = $bindable(false) }: Props = $props();

  const accountId = $derived(parseInt($page.params.accountId ?? '0', 10));

  // State
  let file = $state<File | null>(null);
  let isImporting = $state(false);
  let result = $state<{
    success: boolean;
    total: number;
    failed: number;
  } | null>(null);
  let error = $state<string | null>(null);
  let fileInput = $state<HTMLInputElement>();

  const dispatch = createEventDispatcher<{
    imported: { total: number; failed: number };
  }>();

  function handleFileSelect(event: Event) {
    const input = event.target as HTMLInputElement;
    file = input.files?.[0] || null;
    error = null;
    result = null;
  }

  function handleDrop(event: DragEvent) {
    event.preventDefault();
    const droppedFile = event.dataTransfer?.files[0];
    if (droppedFile && droppedFile.type === 'text/csv') {
      file = droppedFile;
      error = null;
      result = null;
    } else {
      error = 'Please upload a CSV file';
    }
  }

  function handleDragOver(event: DragEvent) {
    event.preventDefault();
  }

  async function handleImport() {
    if (!file) return;

    try {
      isImporting = true;
      error = null;
      result = await importContacts(accountId, file);
      dispatch('imported', { total: result.total, failed: result.failed });
    } catch (err: any) {
      error = err.message || 'Failed to import contacts';
    } finally {
      isImporting = false;
    }
  }

  function resetAndClose() {
    file = null;
    result = null;
    error = null;
    open = false;
  }
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="sm:max-w-[450px]">
    <Dialog.Header>
      <Dialog.Title class="flex items-center gap-2">
        <Upload class="h-5 w-5" />
        Import Contacts
      </Dialog.Title>
      <Dialog.Description>
        Upload a CSV file to import contacts in bulk
      </Dialog.Description>
    </Dialog.Header>

    <div class="py-4">
      {#if result}
        <div class="text-center">
          <CheckCircle class="h-12 w-12 text-green-500 mx-auto mb-3" />
          <h3 class="font-medium text-lg mb-1">Import Complete</h3>
          <p class="text-sm text-muted-foreground mb-4">
            Processed {result.total} contacts with {result.failed} failures
          </p>
        </div>
      {:else if error}
        <div class="text-center">
          <AlertCircle class="h-12 w-12 text-destructive mx-auto mb-3" />
          <h3 class="font-medium text-lg mb-1">Import Failed</h3>
          <p class="text-sm text-muted-foreground mb-4">{error}</p>
        </div>
      {:else if file}
        <div class="text-center p-6 border rounded-lg bg-muted/50">
          <FileSpreadsheet class="h-8 w-8 text-primary mx-auto mb-2" />
          <p class="font-medium text-sm">{file.name}</p>
          <p class="text-xs text-muted-foreground mt-1">
            {(file.size / 1024).toFixed(1)} KB
          </p>
          <Button
            variant="ghost"
            size="sm"
            class="mt-2 text-xs text-destructive hover:text-destructive"
            onclick={() => (file = null)}
          >
            Remove
          </Button>
        </div>
      {:else}
        <div
          class="border-2 border-dashed rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
          ondrop={handleDrop}
          ondragover={handleDragOver}
          onclick={() => fileInput?.click()}
          role="button"
          tabindex="0"
          onkeydown={(e) => e.key === 'Enter' && fileInput?.click()}
        >
          <Upload class="h-8 w-8 text-muted-foreground mx-auto mb-3" />
          <h3 class="font-medium text-sm mb-1">Click to upload</h3>
          <p class="text-xs text-muted-foreground mb-4">
            or drag and drop CSV file here
          </p>
          <input
            type="file"
            accept=".csv"
            class="hidden"
            bind:this={fileInput}
            onchange={handleFileSelect}
          />
        </div>
      {/if}
    </div>

    <Dialog.Footer>
      {#if result}
        <Button onclick={resetAndClose} class="w-full">Done</Button>
      {:else if error}
        <div class="flex gap-2 w-full">
          <Button variant="outline" onclick={resetAndClose} class="flex-1"
            >Close</Button
          >
          <Button onclick={() => (error = null)} class="flex-1">Try Again</Button>
        </div>
      {:else}
        <div class="flex gap-2 w-full">
          <Button variant="outline" onclick={() => (open = false)} class="flex-1"
            >Cancel</Button
          >
          <Button
            onclick={handleImport}
            disabled={!file || isImporting}
            class="flex-1"
          >
            {isImporting ? 'Importing...' : 'Import Contacts'}
          </Button>
        </div>
      {/if}
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
