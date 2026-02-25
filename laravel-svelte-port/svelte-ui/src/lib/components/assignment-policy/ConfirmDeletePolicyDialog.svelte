<script lang="ts">
  import * as AlertDialog from '$lib/components/ui/alert-dialog';

  let {
    open = $bindable(false),
    policyId = null,
    onconfirm,
  }: {
    open?: boolean;
    policyId?: number | null;
    onconfirm?: (id: number) => void;
  } = $props();

  function handleConfirm() {
    if (policyId != null) {
      onconfirm?.(policyId);
    }
    open = false;
  }

  function handleCancel() {
    open = false;
  }
</script>

<AlertDialog.Root bind:open>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Assignment Policy</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete this assignment policy? This action
        cannot be undone and all associated inbox assignments will be removed.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel onclick={handleCancel}>Cancel</AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        onclick={handleConfirm}
      >
        Delete Policy
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
