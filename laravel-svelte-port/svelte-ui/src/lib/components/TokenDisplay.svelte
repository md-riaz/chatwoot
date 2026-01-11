<script lang="ts">
  import { Eye, EyeOff, Copy } from 'lucide-svelte';
  import { toast } from 'svelte-sonner';
  import Button from '$lib/components/ui/button/button.svelte';

  interface Props {
    token: string;
    masked?: boolean;
    size?: 'sm' | 'default';
    showCopyButton?: boolean;
  }

  let { token, masked = true, size = 'default', showCopyButton = true }: Props = $props();
  let showToken = $state(false);

  // Update showToken when masked prop changes
  $effect(() => {
    showToken = !masked;
  });

  function toggleVisibility() {
    showToken = !showToken;
  }

  async function copyToken() {
    try {
      await navigator.clipboard.writeText(token);
      toast.success('Token copied to clipboard');
    } catch (error) {
      toast.error('Failed to copy token');
    }
  }

  function maskToken(token: string): string {
    if (!token || token.length < 8) return '••••••••';
    return '••••••••' + token.slice(-8);
  }
</script>

<div class="flex items-center gap-2">
  <code class="text-sm font-mono bg-slate-2 px-2 py-1 rounded flex-1 min-w-0">
    {showToken ? token : maskToken(token)}
  </code>
  
  <Button
    variant="ghost"
    size={size === 'sm' ? 'sm' : 'icon'}
    on:click={toggleVisibility}
    title={showToken ? 'Hide token' : 'Show token'}
  >
    {#if showToken}
      <EyeOff class="h-3 w-3" />
    {:else}
      <Eye class="h-3 w-3" />
    {/if}
  </Button>
  
  {#if showToken && showCopyButton}
    <Button
      variant="ghost"
      size={size === 'sm' ? 'sm' : 'icon'}
      on:click={copyToken}
      title="Copy token"
    >
      <Copy class="h-3 w-3" />
    </Button>
  {/if}
</div>