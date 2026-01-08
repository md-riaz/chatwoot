<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  type ButtonVariant = 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';

  let {
    label = 'Click',
    confirmLabel = 'Confirm?',
    color = 'default' as ButtonVariant,
    confirmColor = 'destructive' as ButtonVariant,
    disabled = false,
    class: className = '',
    onclick = () => {},
    ...restProps
  }: {
    label?: string;
    confirmLabel?: string;
    color?: ButtonVariant;
    confirmColor?: ButtonVariant;
    disabled?: boolean;
    class?: string;
    onclick?: () => void;
  } = $props();

  let isConfirming = $state(false);
  let timeoutId: ReturnType<typeof setTimeout> | null = null;

  function handleClick() {
    if (isConfirming) {
      onclick();
      isConfirming = false;
      if (timeoutId) clearTimeout(timeoutId);
    } else {
      isConfirming = true;
      timeoutId = setTimeout(() => {
        isConfirming = false;
      }, 3000);
    }
  }

  function handleBlur() {
    if (isConfirming) {
      isConfirming = false;
      if (timeoutId) clearTimeout(timeoutId);
    }
  }
</script>

<Button
  variant={isConfirming ? confirmColor : color}
  {disabled}
  class={cn(className)}
  onclick={handleClick}
  onblur={handleBlur}
  {...restProps}
>
  {isConfirming ? confirmLabel : label}
</Button>
