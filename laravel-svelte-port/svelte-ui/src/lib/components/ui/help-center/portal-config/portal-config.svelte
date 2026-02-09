<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Card } from '$lib/components/ui/card';
  import * as Select from '$lib/components/ui/select';

  interface PortalConfig {
    siteName: string;
    description: string;
    logo: string;
    customDomain: string;
    primaryColor: string;
    fontFamily: string;
    seoTitle: string;
    seoDescription: string;
  }

  interface Props {
    config?: PortalConfig;
    onSave?: (config: PortalConfig) => void;
    saving?: boolean;
  }

  let {
    config = {
      siteName: '',
      description: '',
      logo: '',
      customDomain: '',
      primaryColor: '#3B82F6',
      fontFamily: 'Inter',
      seoTitle: '',
      seoDescription: ''
    },
    onSave = () => {},
    saving = false
  }: Props = $props();

  const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
  const fonts = ['Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat'];

  let fontFamilyValue = $state<string>('');
  
  // Font display label
  const fontLabel = $derived(fontFamilyValue || 'Select font');

  $effect(() => {
    config.fontFamily = fontFamilyValue;
  });

  $effect(() => {
    fontFamilyValue = config.fontFamily;
  });

  function handleSave() {
    onSave(config);
  }
</script>

<div class="w-full max-w-4xl mx-auto space-y-6 p-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold">Portal Configuration</h2>
    <Button onclick={handleSave} disabled={saving}>
      {saving ? 'Saving...' : 'Save Changes'}
    </Button>
  </div>

  <div class="grid gap-6">
    <Card class="p-6 space-y-4">
      <h3 class="text-lg font-semibold">General Settings</h3>
      
      <div class="space-y-2">
        <Label for="siteName">Site Name*</Label>
        <Input id="siteName" bind:value={config.siteName} placeholder="My Help Center" />
      </div>

      <div class="space-y-2">
        <Label for="description">Description</Label>
        <Textarea id="description" bind:value={config.description} placeholder="Help center description" rows={3} />
      </div>

      <div class="space-y-2">
        <Label for="logo">Logo URL</Label>
        <Input id="logo" bind:value={config.logo} placeholder="https://example.com/logo.png" />
      </div>

      <div class="space-y-2">
        <Label for="customDomain">Custom Domain</Label>
        <Input id="customDomain" bind:value={config.customDomain} placeholder="help.example.com" />
      </div>
    </Card>

    <Card class="p-6 space-y-4">
      <h3 class="text-lg font-semibold">Theme Settings</h3>
      
      <div class="space-y-2">
        <Label>Primary Color</Label>
        <div class="flex gap-2">
          {#each colors as color}
            <button
              type="button"
              class="w-10 h-10 rounded-md border-2 transition-all {config.primaryColor === color ? 'scale-110 border-primary' : 'border-transparent'}"
              style="background-color: {color}"
              onclick={() => config.primaryColor = color}
              aria-label="Select color {color}"
            ></button>
          {/each}
        </div>
      </div>

      <div class="space-y-2">
        <Label for="fontFamily">Font Family</Label>
        <Select.Root bind:value={fontFamilyValue} type="single">
          <Select.Trigger id="fontFamily">
            {fontLabel}
          </Select.Trigger>
          <Select.Content>
            {#each fonts as font}
              <Select.Item value={font} label={font}>{font}</Select.Item>
            {/each}
          </Select.Content>
        </Select.Root>
      </div>
    </Card>

    <Card class="p-6 space-y-4">
      <h3 class="text-lg font-semibold">SEO Settings</h3>
      
      <div class="space-y-2">
        <Label for="seoTitle">SEO Title</Label>
        <Input id="seoTitle" bind:value={config.seoTitle} placeholder="Help Center | My Company" />
      </div>

      <div class="space-y-2">
        <Label for="seoDescription">SEO Description</Label>
        <Textarea id="seoDescription" bind:value={config.seoDescription} placeholder="Find answers to your questions" rows={2} />
      </div>
    </Card>
  </div>
</div>
