<script lang="ts">
  import SectionLayout from './SectionLayout.svelte';
  import { Input } from "$lib/components/ui/input";
  import { Button } from "$lib/components/ui/button";
  import { Copy } from "lucide-svelte";
  import { authStore } from '$lib/stores/auth.svelte';
  import { toast } from "svelte-sonner";
  import { _ } from '$lib/i18n';

  let accountId = $derived(authStore.currentAccount?.id?.toString() || '');

  function copyToClipboard() {
    navigator.clipboard.writeText(accountId);
    toast.success("Account ID copied to clipboard");
  }
</script>

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.ACCOUNT_ID.TITLE')}
  description={$_('GENERAL_SETTINGS.FORM.ACCOUNT_ID.NOTE')}
  withBorder
>
  <div class="flex items-center gap-2 max-w-md">
    <div class="relative w-full">
      <Input readonly value={accountId} class="pr-10" />
      <Button 
        variant="ghost" 
        size="icon" 
        class="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
        onclick={copyToClipboard}
      >
        <Copy class="h-4 w-4 text-muted-foreground" />
      </Button>
    </div>
  </div>
</SectionLayout>
