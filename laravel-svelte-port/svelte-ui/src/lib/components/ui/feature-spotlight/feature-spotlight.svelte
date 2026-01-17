<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';

  let {
    title = '',
    description = '',
    actionLabel = 'Learn More',
    dismissLabel = 'Dismiss',
    isNew = false,
    class: className = '',
    onAction = () => {},
    onDismiss = () => {},
    ...restProps
  }: {
    title?: string;
    description?: string;
    actionLabel?: string;
    dismissLabel?: string;
    isNew?: boolean;
    class?: string;
    onAction?: () => void;
    onDismiss?: () => void;
  } = $props();
</script>

<div
  class={cn(
    'relative p-4 bg-linear-to-r from-primary/10 to-primary/5 border border-primary/20 rounded-lg',
    className
  )}
  {...restProps}
>
  <div class="flex items-start gap-4">
    <div class="flex-1">
      <div class="flex items-center gap-2 mb-1">
        <h3 class="font-semibold">{title}</h3>
        {#if isNew}
          <Badge variant="default" class="text-xs">New</Badge>
        {/if}
      </div>
      {#if description}
        <p class="text-sm text-muted-foreground mb-3">
          {description}
        </p>
      {/if}
      <div class="flex items-center gap-2">
        <Button size="sm" onclick={onAction}>
          {actionLabel}
        </Button>
        <Button variant="ghost" size="sm" onclick={onDismiss}>
          {dismissLabel}
        </Button>
      </div>
    </div>
    
    <!-- Decorative element -->
    <div class="hidden sm:block w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
      <span class="text-2xl">✨</span>
    </div>
  </div>
</div>
