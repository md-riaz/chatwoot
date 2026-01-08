<script lang="ts" context="module">
  export type FormMessageProps = {
    error?: string | string[];
    class?: string;
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  
  let {
    error,
    class: className,
    ...restProps
  }: FormMessageProps = $props();
  
  let errors = $derived(
    error ? (Array.isArray(error) ? error : [error]) : []
  );
</script>

{#if errors.length > 0}
  <div class={cn('text-sm font-medium text-destructive', className)} {...restProps}>
    {#each errors as err}
      <p>{err}</p>
    {/each}
  </div>
{/if}
