<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    title: string;
    color: string;
    selected?: boolean;
    removable?: boolean;
    class?: string;
    onClick?: () => void;
    onRemove?: () => void;
  }

  let {
    title,
    color,
    selected = false,
    removable = false,
    class: className = '',
    onClick = () => {},
    onRemove = () => {},
  }: Props = $props();
</script>

<button
  type="button"
  onclick={onClick}
  class={cn(
    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs transition-colors',
    selected ? 'ring-2 ring-primary ring-offset-1' : 'hover:bg-muted/40',
    className
  )}
  style="background-color: {color}20; border-color: {color}; color: {color}"
>
  <span class="h-2 w-2 rounded-full" style="background-color: {color}"></span>
  <span>{title}</span>
  {#if removable}
    <span
      role="button"
      tabindex="0"
      class="ml-1 cursor-pointer hover:opacity-70"
      onclick={event => {
        event.stopPropagation();
        onRemove();
      }}
      onkeydown={event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          event.stopPropagation();
          onRemove();
        }
      }}
    >
      ×
    </span>
  {/if}
</button>
