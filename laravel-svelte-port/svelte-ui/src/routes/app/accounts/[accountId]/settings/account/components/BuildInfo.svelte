<script lang="ts">
  import { globalConfig } from '$lib/stores/globalConfig.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { toast } from "svelte-sonner";
  import { _ } from '$lib/i18n';
  // import semver from 'semver'; // TODO: Fix semver installation or use alternative

  let latestChatwootVersion = $derived(authStore.currentAccount?.latestChatwootVersion);
  let appVersion = $derived(globalConfig.get('appVersion'));
  let displayManifest = $derived(globalConfig.get('displayManifest'));
  let gitSha = $derived(globalConfig.get('gitSha')?.substring(0, 7));

  let hasAnUpdateAvailable = $derived.by(() => {
    if (!latestChatwootVersion || !appVersion) return false;
    // Simple check for now until semver is available
    return latestChatwootVersion !== appVersion; 
    // if (!semver.valid(latestChatwootVersion)) return false;
    // return semver.lt(appVersion, latestChatwootVersion);
  });

  function copyGitSha() {
    navigator.clipboard.writeText(globalConfig.get('gitSha'));
    toast.success("Build SHA copied to clipboard");
  }
</script>

<div class="p-4 text-sm text-center">
  {#if hasAnUpdateAvailable && displayManifest}
    <div class="mb-2">
      {$_('GENERAL_SETTINGS.UPDATE_CHATWOOT', {
        values: { latestChatwootVersion }
      })}
    </div>
  {/if}
  <div class="divide-x divide-border flex justify-center items-center">
    <span class="px-2">v{appVersion}</span>
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <span
      class="px-2 cursor-pointer hover:underline text-muted-foreground"
      title={$_('COMPONENTS.CODE.BUTTON_TEXT')}
      onclick={copyGitSha}
      role="button"
      tabindex="0"
    >
      Build {gitSha}
    </span>
  </div>
</div>
