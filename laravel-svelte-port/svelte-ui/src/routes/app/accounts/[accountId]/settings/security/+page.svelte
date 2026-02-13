<script lang="ts">
  import * as Card from '$lib/components/ui/card';
  import { Switch } from '$lib/components/ui/switch';
  import { Label } from '$lib/components/ui/label';
  import { Button } from '$lib/components/ui/button';
  import { authStore } from '$lib/stores/auth.svelte';

  let samlEnforced = $state(false);
  let mfaRecommended = $state(true);
  let sessionTimeout = $state(true);
  let saving = $state(false);
  let savedMessage = $state<string | null>(null);

  $effect(() => {
    const preferences = authStore.uiSettings?.securityPreferences;
    if (preferences) {
      samlEnforced = !!preferences.samlEnforced;
      mfaRecommended = !!preferences.mfaRecommended;
      sessionTimeout = !!preferences.sessionTimeout;
    }
  });

  async function savePreferences() {
    saving = true;
    await authStore.updateUISettings({
      ...authStore.uiSettings,
      securityPreferences: {
        samlEnforced,
        mfaRecommended,
        sessionTimeout,
      },
    });
    savedMessage = 'Security preferences updated.';
    saving = false;
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Security</h1>
    <p class="text-muted-foreground mt-2">
      Configure SSO, MFA policy, and session security defaults.
    </p>
  </div>

  {#if savedMessage}
    <div
      class="rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800"
    >
      {savedMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title>Authentication Policy</Card.Title>
      <Card.Description
        >Apply account-wide security baselines for all team members.</Card.Description
      >
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="flex items-center justify-between gap-3">
        <div>
          <Label for="saml-enforced">Enforce SAML for administrators</Label>
          <p class="text-sm text-muted-foreground">
            Require administrators to sign in through your identity provider.
          </p>
        </div>
        <Switch id="saml-enforced" bind:checked={samlEnforced} />
      </div>
      <div class="flex items-center justify-between gap-3">
        <div>
          <Label for="mfa-recommended">Require MFA enrollment</Label>
          <p class="text-sm text-muted-foreground">
            Prompt users to enable multi-factor authentication before accessing
            settings.
          </p>
        </div>
        <Switch id="mfa-recommended" bind:checked={mfaRecommended} />
      </div>
      <div class="flex items-center justify-between gap-3">
        <div>
          <Label for="session-timeout">Enable idle session timeout</Label>
          <p class="text-sm text-muted-foreground">
            Sign users out automatically after prolonged inactivity.
          </p>
        </div>
        <Switch id="session-timeout" bind:checked={sessionTimeout} />
      </div>
    </Card.Content>
  </Card.Root>

  <div class="flex justify-end">
    <Button onclick={savePreferences} disabled={saving}
      >{saving ? 'Saving...' : 'Save Security Settings'}</Button
    >
  </div>
</div>
