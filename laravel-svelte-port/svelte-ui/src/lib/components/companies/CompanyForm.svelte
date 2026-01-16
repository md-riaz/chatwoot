<script lang="ts">
  /**
   * CompanyForm
   * Form for creating/editing companies
   */
  import { onMount, createEventDispatcher } from 'svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import type { Company, CreateCompanyParams } from '$lib/api/companies';

  interface Props {
    mode: 'create' | 'edit';
    company?: Company | null;
  }

  let { mode, company = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateCompanyParams;
    cancel: void;
  }>();

  let name = $state('');
  let website = $state('');
  let industry = $state('');
  let size = $state('');
  let description = $state('');

  let errors = $state<Record<string, string>>({});
  let isSubmitting = $state(false);

  onMount(() => {
    // If editing, populate form with company data
    if (mode === 'edit' && company) {
      name = company.name;
      website = company.website || '';
      industry = company.industry || '';
      size = company.size?.toString() || '';
      description = company.description || '';
    }
  });

  function validateWebsite(url: string): boolean {
    if (!url) return true; // Website is optional
    
    try {
      const urlObj = new URL(url);
      return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
    } catch {
      return false;
    }
  }

  function validateForm(): boolean {
    errors = {};
    let isValid = true;

    if (!name || name.trim().length < 1) {
      errors.name = 'Company name is required';
      isValid = false;
    }

    if (website && !validateWebsite(website)) {
      errors.website = 'Please enter a valid URL (must start with http:// or https://)';
      isValid = false;
    }

    return isValid;
  }

  function handleSubmit() {
    if (!validateForm()) {
      return;
    }

    isSubmitting = true;

    const companyData: CreateCompanyParams = {
      name: name.trim(),
      website: website.trim() || undefined,
      industry: industry.trim() || undefined,
      size: size || undefined,
      description: description.trim() || undefined,
    };

    dispatch('submit', companyData);
    isSubmitting = false;
  }

  function handleCancel() {
    dispatch('cancel');
  }
</script>

<div class="space-y-6">
  <div class="space-y-4">
    <div class="space-y-2">
      <Label for="name">Company Name *</Label>
      <Input
        id="name"
        bind:value={name}
        placeholder="e.g., Acme Corporation"
        class={errors.name ? 'border-red-500' : ''}
      />
      {#if errors.name}
        <p class="text-sm text-red-500">{errors.name}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="website">Website</Label>
      <Input
        id="website"
        bind:value={website}
        placeholder="https://example.com"
        type="url"
        class={errors.website ? 'border-red-500' : ''}
      />
      <p class="text-sm text-muted-foreground">
        Company website URL (optional)
      </p>
      {#if errors.website}
        <p class="text-sm text-red-500">{errors.website}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="industry">Industry</Label>
      <Input
        id="industry"
        bind:value={industry}
        placeholder="e.g., Technology, Healthcare, Finance"
      />
      <p class="text-sm text-muted-foreground">
        The industry this company operates in
      </p>
    </div>

    <div class="space-y-2">
      <Label for="size">Company Size (employees)</Label>
      <Input
        id="size"
        type="number"
        bind:value={size}
        placeholder="e.g., 50"
        min="1"
      />
      <p class="text-sm text-muted-foreground">
        Approximate number of employees
      </p>
    </div>

    <div class="space-y-2">
      <Label for="description">Description</Label>
      <Textarea
        id="description"
        bind:value={description}
        placeholder="Brief description of the company..."
        rows={3}
      />
    </div>
  </div>

  <div class="flex justify-end gap-2">
    <Button variant="outline" onclick={handleCancel} disabled={isSubmitting}>
      Cancel
    </Button>
    <Button onclick={handleSubmit} disabled={isSubmitting}>
      {isSubmitting ? 'Saving...' : mode === 'create' ? 'Create Company' : 'Update Company'}
    </Button>
  </div>
</div>
