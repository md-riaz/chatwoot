<script lang="ts">
  import { cn } from '$lib/utils';

  type SheetSide = 'top' | 'right' | 'bottom' | 'left';

  let {
    open = $bindable(false),
    side = 'right' as SheetSide,
    title = '',
    description = '',
    class: className = '',
    children,
    onClose = () => {},
    ...restProps
  }: {
    open?: boolean;
    side?: SheetSide;
    title?: string;
    description?: string;
    class?: string;
    children?: import('svelte').Snippet;
    onClose?: () => void;
  } = $props();

  const sideClasses: Record<SheetSide, string> = {
    top: 'inset-x-0 top-0 border-b',
    right: 'inset-y-0 right-0 border-l w-80',
    bottom: 'inset-x-0 bottom-0 border-t',
    left: 'inset-y-0 left-0 border-r w-80',
  };

  const animationClasses: Record<SheetSide, string> = {
    top: 'animate-in slide-in-from-top',
    right: 'animate-in slide-in-from-right',
    bottom: 'animate-in slide-in-from-bottom',
    left: 'animate-in slide-in-from-left',
  };

  function handleClose() {
    open = false;
    onClose();
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
      handleClose();
    }
  }
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open}
  <!-- Overlay -->
  <div
    class="fixed inset-0 z-50 bg-black/50"
    onclick={handleClose}
    onkeydown={(e: KeyboardEvent) => e.key === 'Escape' && handleClose()}
    role="button"
    tabindex="-1"
  ></div>

  <!-- Sheet -->
  <div
    class={cn(
      'fixed z-50 bg-background p-6',
      sideClasses[side],
      animationClasses[side],
      className
    )}
    role="dialog"
    aria-modal="true"
    {...restProps}
  >
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div>
        {#if title}
          <h2 class="text-lg font-semibold">{title}</h2>
        {/if}
        {#if description}
          <p class="text-sm text-muted-foreground">{description}</p>
        {/if}
      </div>
      <button
        type="button"
        class="p-2 rounded-md hover:bg-accent"
        onclick={handleClose}
      >
        ×
      </button>
    </div>

    <!-- Content -->
    {#if children}
      {@render children()}
    {/if}
  </div>
{/if}
