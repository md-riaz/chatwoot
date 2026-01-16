<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';
  import { Card } from '$lib/components/ui/card';

  export let settings: {
    isPublic: boolean;
    allowIndexing: boolean;
    showBranding: boolean;
    enableAnalytics: boolean;
    analyticsId: string;
    customCss: string;
    customJs: string;
  } = {
    isPublic: true,
    allowIndexing: true,
    showBranding: true,
    enableAnalytics: false,
    analyticsId: '',
    customCss: '',
    customJs: ''
  };

  type SettingsType = typeof settings;
  export let onSave: (settings: SettingsType) => void = () => {};
  export let saving = false;
</script>

<div class="w-full max-w-4xl mx-auto space-y-6 p-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold">Settings</h2>
    <Button onclick={() => onSave(settings)} disabled={saving}>
      {saving ? 'Saving...' : 'Save Changes'}
    </Button>
  </div>

  <div class="grid gap-6">
    <Card class="p-6 space-y-4">
      <h3 class="text-lg font-semibold">Visibility</h3>
      
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label>Public Access</Label>
          <p class="text-sm text-muted-foreground">Allow anyone to view your help center</p>
        </div>
        <Switch bind:checked={settings.isPublic} />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label>Search Engine Indexing</Label>
          <p class="text-sm text-muted-foreground">Allow search engines to index your content</p>
        </div>
        <Switch bind:checked={settings.allowIndexing} />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label>Show Chatwoot Branding</Label>
          <p class="text-sm text-muted-foreground">Display "Powered by Chatwoot" footer</p>
        </div>
        <Switch bind:checked={settings.showBranding} />
      </div>
    </Card>

    <Card class="p-6 space-y-4">
      <h3 class="text-lg font-semibold">Analytics</h3>
      
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label>Enable Analytics</Label>
          <p class="text-sm text-muted-foreground">Track visitor behavior and article views</p>
        </div>
        <Switch bind:checked={settings.enableAnalytics} />
      </div>

      {#if settings.enableAnalytics}
        <div class="space-y-2">
          <Label for="analyticsId">Analytics ID (Google Analytics, Plausible, etc.)</Label>
          <Input id="analyticsId" bind:value={settings.analyticsId} placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX" />
        </div>
      {/if}
    </Card>

    <Card class="p-6 space-y-4">
      <h3 class="text-lg font-semibold">Custom Code</h3>
      
      <div class="space-y-2">
        <Label for="customCss">Custom CSS</Label>
        <Textarea id="customCss" bind:value={settings.customCss} placeholder=".my-class {'{'} color: red; {'}'}" rows={6} class="font-mono text-sm" />
      </div>

      <div class="space-y-2">
        <Label for="customJs">Custom JavaScript</Label>
        <Textarea id="customJs" bind:value={settings.customJs} placeholder="console.log('Hello');" rows={6} class="font-mono text-sm" />
      </div>
    </Card>
  </div>
</div>
