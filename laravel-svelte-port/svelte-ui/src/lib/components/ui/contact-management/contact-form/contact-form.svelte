<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Plus, Trash2, Facebook, Twitter, Linkedin, Instagram, Github } from 'lucide-svelte';
  import { isValidPhoneNumber, type CountryCode } from 'libphonenumber-js';
  import { Avatar } from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import * as Select from '$lib/components/ui/select';
  import CountrySelect from '$lib/components/ui/country-select/country-select.svelte';
  import PhoneInput from '$lib/components/ui/phone-input/phone-input.svelte';
  import type { Contact } from '$lib/api/contacts';

  let { contact = null, serverErrors = {} } = $props<{ contact?: Contact | null, serverErrors?: Record<string, string> }>();

  const dispatch = createEventDispatcher();

  let form = $state({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    company: '',
    city: '',
    countryCode: '',
    description: '',
    tags: [] as string[],
    status: 'active',
    socialProfiles: {
      facebook: '',
      twitter: '',
      linkedin: '',
      github: '',
      instagram: ''
    } as Record<string, string>
  });
  
  let phoneCountry = $state('US');

  let avatarFile = $state<File | null>(null);
  let avatarPreview = $state<string | null>(null);
  let errors = $state<Record<string, string>>({});
  let activeSocials = $state<Set<string>>(new Set());

  const socialNetworks = [
    { key: 'facebook', label: 'Facebook', icon: Facebook, placeholder: 'Username or URL' },
    { key: 'twitter', label: 'Twitter', icon: Twitter, placeholder: 'Username' },
    { key: 'linkedin', label: 'LinkedIn', icon: Linkedin, placeholder: 'Profile URL' },
    { key: 'github', label: 'Github', icon: Github, placeholder: 'Username' },
    { key: 'instagram', label: 'Instagram', icon: Instagram, placeholder: 'Username' }
  ];

  $effect(() => {
    if (contact) {
      form = {
        firstName: contact.name ? contact.name.split(' ')[0] : '',
        lastName: contact.name ? contact.name.split(' ').slice(1).join(' ') : '',
        email: contact.email || '',
        phone: contact.phoneNumber || '',
        company: typeof contact.company === 'string' ? contact.company : (contact.company?.name || ''),
        city: contact.city || '',
        countryCode: contact.countryCode || '',
        description: contact.additionalAttributes?.description || '',
        tags: contact.tags || [],
        status: 'active',
        socialProfiles: {
          facebook: '',
          twitter: '',
          linkedin: '',
          github: '',
          instagram: '',
          ...(contact.additionalAttributes?.social_profiles || {})
        }
      };
      if (contact.countryCode) {
        phoneCountry = contact.countryCode.toUpperCase();
      }
      avatarPreview = contact.avatarUrl || contact.thumbnail || null;
      
      // Initialize active social profiles
      if (contact.additionalAttributes?.social_profiles) {
        Object.entries(contact.additionalAttributes.social_profiles).forEach(([k, v]) => {
          if (v) activeSocials.add(k);
        });
      }
    }
  });

  function addSocialProfile(key: string) {
    activeSocials = new Set([...activeSocials, key]);
  }

  function removeSocialProfile(key: string) {
    const newSet = new Set(activeSocials);
    newSet.delete(key);
    activeSocials = newSet;
    form.socialProfiles[key] = '';
  }

  function onFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) return;
    avatarFile = input.files[0];
    const reader = new FileReader();
    reader.onload = () => {
      avatarPreview = String(reader.result || '');
    };
    reader.readAsDataURL(avatarFile);
  }

  function validate(): boolean {
    errors = {};
    if (!form.firstName || form.firstName.trim().length === 0) {
      errors.firstName = 'First name is required';
    }
    if (form.email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(form.email)) {
      errors.email = 'A valid email is required';
    }
    if (form.phone) {
      // Use the phoneCountry from PhoneInput for validation
      const country = phoneCountry ? phoneCountry.toUpperCase() as CountryCode : undefined;
      if (!isValidPhoneNumber(form.phone, country)) {
        errors.phone = 'Invalid phone number';
      }
    }
    return Object.keys(errors).length === 0;
  }

  export function submit() {
    if (!validate()) return;
    
    // Filter out empty social profiles
    const activeSocialProfiles = Object.entries(form.socialProfiles)
      .filter(([_, value]) => value && value.trim() !== '')
      .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {});

    // Transform form data to match API expectation
    const payload: any = {
      name: `${form.firstName} ${form.lastName}`.trim(),
      email: form.email,
      phoneNumber: form.phone,
      company: form.company,
      city: form.city,
      countryCode: form.countryCode,
      additionalAttributes: {
        description: form.description,
        company_name: form.company,
        city: form.city,
        country_code: form.countryCode,
        social_profiles: activeSocialProfiles
      },
      customAttributes: {}
    };

    if (avatarFile) {
      payload._avatarFile = avatarFile;
    }
    
    dispatch('save', payload);
  }
</script>

<div class="space-y-6 max-h-[80vh] overflow-y-auto pr-2">
  <div class="flex items-center gap-4">
    <Avatar class="h-16 w-16">
      {#if avatarPreview}
        <img src={avatarPreview} alt={form.name || 'avatar'} />
      {:else}
        <div class="flex h-full w-full items-center justify-center bg-primary text-primary-foreground text-xl">
          {form.firstName ? (form.firstName[0] + (form.lastName ? form.lastName[0] : '')) : '??'}
        </div>
      {/if}
    </Avatar>
    <div class="flex flex-col flex-1 gap-2">
      <Label for="contact-avatar" class="text-sm text-muted-foreground">Upload Avatar</Label>
      <input id="contact-avatar" type="file" accept="image/*" onchange={(e: Event) => onFileChange(e)} class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100"/>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="space-y-2">
      <Label for="contact-first-name">First Name <span class="text-destructive">*</span></Label>
      <Input id="contact-first-name" bind:value={form.firstName} placeholder="First name" />
      {#if errors.firstName || serverErrors.name}
        <p class="text-xs text-destructive">{errors.firstName || serverErrors.name}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="contact-last-name">Last Name</Label>
      <Input id="contact-last-name" bind:value={form.lastName} placeholder="Last name" />
    </div>

    <div class="space-y-2">
      <Label for="contact-email">Email</Label>
      <Input id="contact-email" bind:value={form.email} placeholder="name@example.com" type="email" />
      {#if errors.email || serverErrors.email}
        <p class="text-xs text-destructive">{errors.email || serverErrors.email}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label>Phone Number</Label>
      <PhoneInput bind:value={form.phone} bind:country={phoneCountry} />
      {#if errors.phone || serverErrors.phone_number}
        <p class="text-xs text-destructive">{errors.phone || serverErrors.phone_number}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="contact-company">Company</Label>
      <Input id="contact-company" bind:value={form.company} placeholder="Company name" />
      {#if serverErrors.company_name || serverErrors.company}
        <p class="text-xs text-destructive">{serverErrors.company_name || serverErrors.company}</p>
      {/if}
    </div>
    
    <div class="space-y-2">
      <Label for="contact-city">City</Label>
      <Input id="contact-city" bind:value={form.city} placeholder="City" />
      {#if serverErrors.city}
        <p class="text-xs text-destructive">{serverErrors.city}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="contact-country">Country</Label>
      <CountrySelect bind:value={form.countryCode} placeholder="Select country" />
      {#if serverErrors.country_code}
        <p class="text-xs text-destructive">{serverErrors.country_code}</p>
      {/if}
    </div>
  </div>

  <div class="space-y-2">
    <Label for="contact-bio">Bio</Label>
    <Textarea id="contact-bio" bind:value={form.description} placeholder="Add a description..." class="resize-none h-24" />
  </div>

    <div class="space-y-4">
      <div class="flex items-center gap-2">
        <Label>Social Profiles</Label>
      </div>
      
      <!-- Check if we have active profiles first -->
      {#if activeSocials.size > 0}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          {#each socialNetworks as network}
            {#if activeSocials.has(network.key)}
              <div class="relative flex items-center">
                <div class="absolute left-3 text-muted-foreground flex items-center justify-center">
                  <svelte:component this={network.icon} class="h-4 w-4" />
                </div>
                <Input 
                  bind:value={form.socialProfiles[network.key]} 
                  placeholder={network.placeholder}
                  class="pl-9 pr-8"
                />
                <Button 
                  variant="ghost" 
                  size="icon" 
                  class="absolute right-1 h-7 w-7 text-muted-foreground hover:text-destructive"
                  onclick={() => removeSocialProfile(network.key)}
                >
                  <span class="sr-only">Remove</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </Button>
              </div>
            {/if}
          {/each}
        </div>
      {/if}

      <!-- Add Buttons -->
      <div class="flex flex-wrap gap-2">
        {#each socialNetworks as network}
          {#if !activeSocials.has(network.key)}
            <button 
              class="inline-flex items-center gap-2 h-9 px-3 text-sm transition-colors rounded-md bg-muted/50 hover:bg-muted text-muted-foreground hover:text-foreground ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
              onclick={() => addSocialProfile(network.key)}
            >
              <svelte:component this={network.icon} class="h-4 w-4" />
              <span>Add {network.label}</span>
            </button>
          {/if}
        {/each}
      </div>
    </div>


</div>
