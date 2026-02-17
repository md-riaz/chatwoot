<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { cn } from '$lib/utils';
  import { MessageSquare, Lock } from 'lucide-svelte';

  let { mode = 'reply', onModeChange } = $props<{
    mode: 'reply' | 'private';
    onModeChange: (mode: 'reply' | 'private') => void;
  }>();
</script>

<div class="flex items-center gap-1 p-1 bg-muted/50 rounded-lg w-fit">
  <button
    class={cn(
      'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md transition-all',
      mode === 'reply'
        ? 'bg-background text-foreground shadow-sm'
        : 'text-muted-foreground hover:text-foreground hover:bg-background/50'
    )}
    onclick={() => onModeChange('reply')}
  >
    <MessageSquare class="h-4 w-4" />
    Reply
  </button>
  <button
    class={cn(
      'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md transition-all',
      mode === 'private'
        ? 'bg-amber-100 text-amber-900 shadow-sm'
        : 'text-muted-foreground hover:text-foreground hover:bg-background/50'
    )}
    onclick={() => onModeChange('private')}
  >
    <Lock class="h-4 w-4" />
    Private Note
  </button>
</div>
