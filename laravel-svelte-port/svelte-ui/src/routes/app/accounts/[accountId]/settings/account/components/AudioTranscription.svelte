<script lang="ts">
  import SectionLayout from './SectionLayout.svelte';
  import { Switch } from "$lib/components/ui/switch";
  import { authStore } from '$lib/stores/auth.svelte';
  import { toast } from "svelte-sonner";
  import { _ } from '$lib/i18n';

  let currentAccount = $derived(authStore.currentAccount);
  let isEnabled = $state(false);
  let isUpdating = $state(false);

  $effect(() => {
    if (currentAccount?.settings) {
      isEnabled = !!currentAccount.settings.audio_transcriptions;
    }
  });

  async function toggleAudioTranscription(checked: boolean) {
    isEnabled = checked;
    isUpdating = true;
    try {
      // TODO: Implement API update
      // await accountsStore.updateAccount({ audio_transcriptions: checked });
      toast.success($_('GENERAL_SETTINGS.FORM.AUDIO_TRANSCRIPTION.API.SUCCESS'));
    } catch (error) {
      toast.error($_('GENERAL_SETTINGS.FORM.AUDIO_TRANSCRIPTION.API.ERROR'));
      // Revert on error
      isEnabled = !checked;
    } finally {
      isUpdating = false;
    }
  }
</script>

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.AUDIO_TRANSCRIPTION.TITLE')}
  description={$_('GENERAL_SETTINGS.FORM.AUDIO_TRANSCRIPTION.NOTE')}
  withBorder={true}
>
  {#snippet headerActions()}
    <div class="flex justify-end">
      <Switch checked={isEnabled} onCheckedChange={toggleAudioTranscription} disabled={isUpdating} />
    </div>
  {/snippet}
</SectionLayout>
