<script lang="ts">
  import SectionLayout from './SectionLayout.svelte';
  import { Input } from "$lib/components/ui/input";
  import { Textarea } from "$lib/components/ui/textarea";
  import { Switch } from "$lib/components/ui/switch";
  import { Button } from "$lib/components/ui/button";
  import { Label } from "$lib/components/ui/label";
  import * as Select from "$lib/components/ui/select";
  import { authStore } from '$lib/stores/auth.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { toast } from "svelte-sonner";
  import { _ } from '$lib/i18n';

  let currentAccount = $derived(authStore.currentAccount);
  let isEnabled = $state(false);
  let duration = $state(0);
  let message = $state('');
  let ignoreWaiting = $state(false);
  let selectedLabel = $state<string>('');
  let isSubmitting = $state(false);
  
  // Label display
  const selectedLabelDisplay = $derived(
    selectedLabel || $_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.LABEL.PLACEHOLDER')
  );

  // Initialize from account settings
  $effect(() => {
    if (currentAccount?.settings) {
       duration = currentAccount.settings.auto_resolve_after || 0;
       message = currentAccount.settings.auto_resolve_message || '';
       ignoreWaiting = currentAccount.settings.auto_resolve_ignore_waiting || false;
       selectedLabel = currentAccount.settings.auto_resolve_label || '';
       isEnabled = !!duration;
    }
  });

  async function handleSubmit() {
    if (duration < 10) {
      toast.error($_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.ERROR'));
      return;
    }
    
    isSubmitting = true;
    try {
      await authStore.updateAccount({
        autoResolveDuration: duration,
        autoResolveMessage: message,
        autoResolveIgnoreWaiting: ignoreWaiting,
        autoResolveLabel: selectedLabel
      });
      toast.success($_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.API.SUCCESS'));
    } catch (error) {
      toast.error($_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.API.ERROR'));
    } finally {
      isSubmitting = false;
    }
  }

  async function toggleAutoResolve(checked: boolean) {
     if (checked) {
        isEnabled = true;
        if (duration === 0) duration = 10;
     } else {
        isEnabled = false;
        try {
          await authStore.updateAccount({
            autoResolveDuration: 0
          });
          toast.success($_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.API.SUCCESS'));
        } catch (error) {
           toast.error($_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.API.ERROR'));
           isEnabled = true;
        }
     }
  }
</script>

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.TITLE')}
  description={$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.NOTE')}
  hideContent={!isEnabled}
  withBorder
>
  {#snippet headerActions()}
    <div class="flex justify-end">
       <Switch checked={isEnabled} onCheckedChange={toggleAutoResolve} />
    </div>
  {/snippet}

  <form class="grid gap-5" onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
    <div class="grid gap-2">
      <Label>{$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.LABEL')}</Label>
      <div class="text-sm text-muted-foreground mb-1">
        {$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.DURATION.HELP')}
      </div>
      <Input type="number" bind:value={duration} min="0" />
    </div>

    <div class="grid gap-2">
      <Label>{$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.MESSAGE.LABEL')}</Label>
      <div class="text-sm text-muted-foreground mb-1">
        {$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.MESSAGE.HELP')}
      </div>
      <Textarea 
        bind:value={message} 
        placeholder={$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.MESSAGE.PLACEHOLDER')}
      />
    </div>

    <div class="grid gap-2">
      <Label>{$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.PREFERENCES')}</Label>
      <div class="rounded-xl border bg-card text-sm divide-y">
         <div class="p-3 h-12 flex items-center justify-between">
            <span>{$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.IGNORE_WAITING.LABEL')}</span>
            <Switch bind:checked={ignoreWaiting} />
         </div>
         <div class="p-3 h-12 flex items-center justify-between">
            <span>{$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.LABEL.LABEL')}</span>
            <!-- Label Selector placeholder -->
            <Select.Root type="single" bind:value={selectedLabel}>
               <Select.Trigger class="w-[180px]">
                 {selectedLabelDisplay}
               </Select.Trigger>
               <Select.Content>
                  {#each labelsStore.allLabels as label}
                    <Select.Item value={label.title} label={label.title}>{label.title}</Select.Item>
                  {/each}
               </Select.Content>
            </Select.Root>
         </div>
      </div>
    </div>

    <div class="flex gap-2">
      <Button type="submit" disabled={isSubmitting}>
        {$_('GENERAL_SETTINGS.FORM.AUTO_RESOLVE.UPDATE_BUTTON')}
      </Button>
    </div>
  </form>
</SectionLayout>
