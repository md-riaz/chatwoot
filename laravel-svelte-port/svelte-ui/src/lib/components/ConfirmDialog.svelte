<script lang="ts">
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  
  interface Props {
    open?: boolean;
    title: string;
    description: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    onConfirm?: () => void;
    onCancel?: () => void;
  }
  
  let {
    open = $bindable(false),
    title,
    description,
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    variant = 'default',
    onConfirm,
    onCancel
  }: Props = $props();
  
  function handleConfirm() {
    onConfirm?.();
    open = false;
  }
  
  function handleCancel() {
    onCancel?.();
    open = false;
  }
</script>

<Dialog.Root bind:open>
  <Dialog.Content>
    <Dialog.Header>
      <Dialog.Title>{title}</Dialog.Title>
      <Dialog.Description>{description}</Dialog.Description>
    </Dialog.Header>
    <Dialog.Footer>
      <Button variant="outline" onclick={handleCancel}>
        {cancelText}
      </Button>
      <Button {variant} onclick={handleConfirm}>
        {confirmText}
      </Button>
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
