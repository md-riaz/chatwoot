<script lang="ts">
  import SectionLayout from './SectionLayout.svelte';
  import { Button } from "$lib/components/ui/button";
  import * as AlertDialog from "$lib/components/ui/alert-dialog";
  import { authStore } from '$lib/stores/auth.svelte';
  import { toast } from "svelte-sonner";
  import { _ } from '$lib/i18n';

  let currentAccount = $derived(authStore.currentAccount);
  let isMarkedForDeletion = $derived(!!currentAccount?.custom_attributes?.marked_for_deletion_at);
  let markedForDeletionDate = $derived(
    currentAccount?.custom_attributes?.marked_for_deletion_at 
      ? new Date(currentAccount.custom_attributes.marked_for_deletion_at) 
      : null
  );
  let markedForDeletionReason = $derived(
    currentAccount?.custom_attributes?.marked_for_deletion_reason || 'manual_deletion'
  );

  let formattedDeletionDate = $derived(markedForDeletionDate ? markedForDeletionDate.toLocaleString() : '');

  let markedForDeletionMessage = $derived.by(() => {
     const params = { values: { deletionDate: formattedDeletionDate } };
     if (markedForDeletionReason === 'manual_deletion') {
       return $_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.SCHEDULED_DELETION.MESSAGE_MANUAL', params);
     }
     return $_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.SCHEDULED_DELETION.MESSAGE_INACTIVITY', params);
  });

  let showDeletePopup = $state(false);
  let isUpdating = $state(false);

  async function markAccountForDeletion() {
    isUpdating = true;
    try {
      await authStore.toggleAccountDeletion('delete');
       toast.success($_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.SUCCESS'));
       showDeletePopup = false;
    } catch (error) {
       toast.error($_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.FAILURE'));
    } finally {
      isUpdating = false;
    }
  }

  async function clearDeletionMark() {
     isUpdating = true;
    try {
      await authStore.toggleAccountDeletion('undelete');
       toast.success($_('GENERAL_SETTINGS.UPDATE.SUCCESS'));
    } catch (error) {
       toast.error($_('GENERAL_SETTINGS.UPDATE.ERROR'));
    } finally {
      isUpdating = false;
    }
  }
</script>

<SectionLayout
  title={$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.TITLE')}
  description={$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.NOTE')}
  withBorder
>
  {#if isMarkedForDeletion}
    <div class="p-4 bg-destructive/10 rounded-md">
      <p class="mb-4 text-sm text-destructive-foreground">
        {markedForDeletionMessage}
      </p>
      <Button
        variant="destructive"
        disabled={isUpdating}
        onclick={clearDeletionMark}
      >
        {$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.SCHEDULED_DELETION.CLEAR_BUTTON')}
      </Button>
    </div>
  {:else}
    <div>
      <Button
        variant="destructive"
        onclick={() => showDeletePopup = true}
      >
        {$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.BUTTON_TEXT')}
      </Button>
    </div>
  {/if}

  <AlertDialog.Root bind:open={showDeletePopup}>
    <AlertDialog.Content>
      <AlertDialog.Header>
        <AlertDialog.Title>
            {$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.CONFIRM.TITLE')}
        </AlertDialog.Title>
        <AlertDialog.Description>
            {$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.CONFIRM.MESSAGE')}
        </AlertDialog.Description>
      </AlertDialog.Header>
      <AlertDialog.Footer>
        <AlertDialog.Cancel>
            {$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.CONFIRM.DISMISS')}
        </AlertDialog.Cancel>
        <AlertDialog.Action onclick={markAccountForDeletion}>
            {$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.CONFIRM.BUTTON_TEXT')}
        </AlertDialog.Action>
      </AlertDialog.Footer>
    </AlertDialog.Content>
  </AlertDialog.Root>
</SectionLayout>
