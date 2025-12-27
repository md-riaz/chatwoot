<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';

  interface Props {
    onAdd?: (rule: string) => void;
    placeholder?: string;
    class?: string;
  }

  let { onAdd, placeholder = 'Type a new rule...', class: className }: Props = $props();
  let value = $state('');

  function handleAdd() {
    if (value.trim()) {
      onAdd?.(value);
      value = '';
    }
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
      handleAdd();
    }
  }
</script>

<div class={cn('flex gap-2', className)}>
  <Input 
    bind:value 
    {placeholder} 
    onkeydown={handleKeydown}
    class="flex-1"
  />
  <Button onclick={handleAdd} disabled={!value.trim()}>
    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Add
  </Button>
</div>
