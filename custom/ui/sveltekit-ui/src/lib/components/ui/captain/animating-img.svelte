<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    src: string;
    alt?: string;
    animate?: boolean;
    class?: string;
  }

  let { src, alt = '', animate = true, class: className }: Props = $props();
</script>

<div class={cn('relative', className)}>
  <img 
    {src} 
    {alt} 
    class={cn(
      'rounded-lg',
      animate && 'animate-pulse'
    )} 
  />
  {#if animate}
    <div class="absolute inset-0 bg-gradient-to-t from-woot-500/20 to-transparent rounded-lg animate-pulse" />
  {/if}
</div>
