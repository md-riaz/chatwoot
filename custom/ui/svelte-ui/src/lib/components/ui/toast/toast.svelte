<script lang="ts">
  import { cn } from '$lib/utils';

  type ToastVariant = 'default' | 'success' | 'destructive' | 'warning';

  let {
    title = '',
    description = '',
    variant = 'default' as ToastVariant,
    open = $bindable(true),
    duration = 5000,
    class: className = '',
    onClose = () => {},
    ...restProps
  }: {
    title?: string;
    description?: string;
    variant?: ToastVariant;
    open?: boolean;
    duration?: number;
    class?: string;
    onClose?: () => void;
  } = $props();

  const variantClasses: Record<ToastVariant, string> = {
    default: 'bg-background border',
    success: 'bg-green-50 border-green-200 dark:bg-green-950 dark:border-green-800',
    destructive: 'bg-red-50 border-red-200 dark:bg-red-950 dark:border-red-800',
    warning: 'bg-yellow-50 border-yellow-200 dark:bg-yellow-950 dark:border-yellow-800',
  };

  const iconMap: Record<ToastVariant, string> = {
    default: 'ℹ️',
    success: '✅',
    destructive: '❌',
    warning: '⚠️',
  };

  $effect(() => {
    if (open && duration > 0) {
      const timer = setTimeout(() => {
        open = false;
        onClose();
      }, duration);
      return () => clearTimeout(timer);
    }
  });

  function handleClose() {
    open = false;
    onClose();
  }
</script>

{#if open}
  <div
    class={cn(
      'fixed bottom-4 right-4 z-50 w-80 rounded-lg border p-4 shadow-lg',
      'animate-in slide-in-from-bottom-5',
      variantClasses[variant],
      className
    )}
    role="alert"
    {...restProps}
  >
    <div class="flex items-start gap-3">
      <span class="text-lg">{iconMap[variant]}</span>
      <div class="flex-1">
        {#if title}
          <p class="font-medium text-sm">{title}</p>
        {/if}
        {#if description}
          <p class="text-sm text-muted-foreground mt-1">{description}</p>
        {/if}
      </div>
      <button
        type="button"
        class="text-muted-foreground hover:text-foreground"
        onclick={handleClose}
      >
        ×
      </button>
    </div>
  </div>
{/if}
