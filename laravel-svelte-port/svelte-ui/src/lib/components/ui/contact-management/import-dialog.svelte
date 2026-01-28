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

  const accountId = $derived(parseInt($page.params.accountId, 10));

  // State
  let file = $state<File | null>(null);
  let isImporting = $state(false);
  let result = $state<{
    success: boolean;
    total: number;
    failed: number;
  } | null>(null);
  let error = $state<string | null>(null);
  let fileInput: HTMLInputElement;

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
        <!-- Success state -->
        <div class="text-center py-6">
          <CheckCircle class="h-12 w-12 text-green-500 mx-auto mb-3" />
          <h3 class="font-medium text-lg mb-1">Import Complete</h3>
          <p class="text-sm text-muted-foreground">
            Successfully imported {result.total - result.failed} of {result.total}
            contacts
          </p>
          {#if result.failed > 0}
            <p class="text-sm text-amber-600 mt-1">
              {result.failed} contact{result.failed > 1 ? 's' : ''} failed to import
            </p>
          {/if}
        </div>
      {:else}
        <!-- Upload area -->
        <input
          type="file"
          accept=".csv"
          class="hidden"
          bind:this={fileInput}
          onchange={handleFileSelect}
        />

        <button
          type="button"
          onclick={() => fileInput?.click()}
          ondrop={handleDrop}
          ondragover={handleDragOver}
          class="w-full border-2 border-dashed rounded-lg p-8 text-center hover:border-primary/50 hover:bg-muted/30 transition-colors cursor-pointer"
        >
          {#if file}
            <FileSpreadsheet class="h-10 w-10 text-primary mx-auto mb-3" />
            <p class="font-medium">{file.name}</p>
            <p class="text-sm text-muted-foreground mt-1">
              {(file.size / 1024).toFixed(1)} KB
            </p>
          {:else}
            <Upload class="h-10 w-10 text-muted-foreground mx-auto mb-3" />
            <p class="font-medium">Drop your CSV file here</p>
            <p class="text-sm text-muted-foreground mt-1">or click to browse</p>
          {/if}
        </button>

        {#if error}
          <div class="flex items-center gap-2 text-destructive text-sm mt-3">
            <AlertCircle class="h-4 w-4" />
            {error}
          </div>
        {/if}

        <div class="mt-4 text-xs text-muted-foreground">
          <p class="font-medium mb-1">CSV Format:</p>
          <p>name, email, phone_number, company, city, country</p>
        </div>
      {/if}
    </div>

    <Dialog.Footer>
      {#if result}
        <Button onclick={resetAndClose}>Done</Button>
      {:else}
        <Button variant="outline" onclick={resetAndClose}>Cancel</Button>
        <Button onclick={handleImport} disabled={!file || isImporting}>
          {isImporting ? 'Importing...' : 'Import Contacts'}
        </Button>
      {/if}
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
