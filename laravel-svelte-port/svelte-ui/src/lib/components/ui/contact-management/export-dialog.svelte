<script lang="ts">
  /**
   * Export Contacts Dialog
   * Download contacts as CSV
   */
  import {
    Download,
    FileSpreadsheet,
    CheckCircle,
    AlertCircle,
  } from 'lucide-svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { exportContacts } from '$lib/api/contacts';
  import { page } from '$app/stores';

  // Props
  interface Props {
    open?: boolean;
    contactCount?: number;
  }

  let { open = $bindable(false), contactCount = 0 }: Props = $props();

  const accountId = $derived(parseInt($page.params.accountId ?? '0', 10));

  // State
  let isExporting = $state(false);
  let isComplete = $state(false);
  let error = $state<string | null>(null);

  async function handleExport() {
    try {
      isExporting = true;
      error = null;

      const blob = await exportContacts(accountId);

      // Create download link
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `contacts_${new Date().toISOString().split('T')[0]}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      isComplete = true;
    } catch (err: any) {
      error = err.message || 'Failed to export contacts';
    } finally {
      isExporting = false;
    }
  }

  function resetAndClose() {
    isComplete = false;
    error = null;
    open = false;
  }
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="sm:max-w-[400px]">
    <Dialog.Header>
      <Dialog.Title class="flex items-center gap-2">
        <Download class="h-5 w-5" />
        Export Contacts
      </Dialog.Title>
    </Dialog.Header>

    <div class="py-6">
      {#if isComplete}
        <div class="text-center">
          <CheckCircle class="h-12 w-12 text-green-500 mx-auto mb-3" />
          <h3 class="font-medium text-lg mb-1">Export Complete</h3>
          <p class="text-sm text-muted-foreground">
            Your contacts have been downloaded
          </p>
        </div>
      {:else if error}
        <div class="text-center">
          <AlertCircle class="h-12 w-12 text-destructive mx-auto mb-3" />
          <h3 class="font-medium text-lg mb-1">Export Failed</h3>
          <p class="text-sm text-muted-foreground">{error}</p>
        </div>
      {:else}
        <div class="text-center">
          <FileSpreadsheet
            class="h-12 w-12 text-muted-foreground mx-auto mb-3"
          />
          <p class="text-muted-foreground">
            {#if contactCount > 0}
              Export {contactCount} contact{contactCount !== 1 ? 's' : ''} as CSV
            {:else}
              Export all contacts as CSV
            {/if}
          </p>
        </div>
      {/if}
    </div>

    <Dialog.Footer>
      {#if isComplete}
        <Button onclick={resetAndClose} class="w-full">Done</Button>
      {:else if error}
        <div class="flex gap-2 w-full">
          <Button variant="outline" onclick={resetAndClose} class="flex-1"
            >Close</Button
          >
          <Button onclick={handleExport} class="flex-1">Try Again</Button>
        </div>
      {:else}
        <div class="flex gap-2 w-full">
          <Button variant="outline" onclick={() => (open = false)} class="flex-1"
            >Cancel</Button
          >
          <Button
            onclick={handleExport}
            disabled={isExporting}
            class="flex-1"
          >
            {isExporting ? 'Exporting...' : 'Export CSV'}
          </Button>
        </div>
      {/if}
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
