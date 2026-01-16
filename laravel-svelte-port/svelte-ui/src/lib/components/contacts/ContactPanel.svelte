<script lang="ts">
  /**
   * ContactPanel - Contact information sidebar
   * Shows contact details, custom attributes, and previous conversations
   */
  
  import { Mail, Phone, MapPin, Building, X } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as Card from '$lib/components/ui/card';
  import * as Tabs from '$lib/components/ui/tabs';
  import { Badge } from '$lib/components/ui/badge';
  import * as CustomAttributes from '$lib/components/ui/custom-attributes';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import type { Contact } from '$lib/api/contacts';
  
  interface Props {
    contactId: number;
    onClose?: () => void;
  }
  
  let { contactId, onClose }: Props = $props();
  
  // Reactive store access
  const contact = $derived(
    contactsStore.allContacts.find(c => c.id === contactId)
  );
  
  const displayName = $derived(contact?.name || 'Unknown Contact');
  const email = $derived(contact?.email || '');
  const phoneNumber = $derived(contact?.phoneNumber || '');
  const availabilityStatus = $derived(contact?.availabilityStatus);
  const customAttributes = $derived(contact?.customAttributes || {});
</script>

<div class="flex flex-col h-full bg-background border-l">
  <!-- Header -->
  <div class="flex items-center justify-between p-4 border-b">
    <h3 class="text-lg font-semibold">Contact Details</h3>
    {#if onClose}
      <Button variant="ghost" size="icon" onclick={onClose}>
        <X class="h-4 w-4" />
      </Button>
    {/if}
  </div>
  
  {#if contact}
    <div class="flex-1 overflow-y-auto">
      <!-- Contact Info Card -->
      <div class="p-4 space-y-4">
        <!-- Avatar and Name -->
        <div class="flex flex-col items-center text-center space-y-3">
          <Avatar.Root class="h-20 w-20">
            <Avatar.Image src={contact.thumbnail || ''} alt={displayName} />
            <Avatar.Fallback class="text-2xl">
              {displayName.charAt(0).toUpperCase()}
            </Avatar.Fallback>
          </Avatar.Root>
          
          <div>
            <h4 class="text-xl font-semibold">{displayName}</h4>
            {#if availabilityStatus}
              <Badge variant="secondary" class="mt-1">
                {availabilityStatus}
              </Badge>
            {/if}
          </div>
        </div>
        
        <!-- Contact Information -->
        <Card.Root>
          <Card.Header>
            <Card.Title class="text-sm">Contact Information</Card.Title>
          </Card.Header>
          <Card.Content class="space-y-3">
            {#if email}
              <div class="flex items-start gap-3">
                <Mail class="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-muted-foreground">Email</p>
                  <a 
                    href="mailto:{email}" 
                    class="text-sm hover:underline break-all"
                  >
                    {email}
                  </a>
                </div>
              </div>
            {/if}
            
            {#if phoneNumber}
              <div class="flex items-start gap-3">
                <Phone class="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-muted-foreground">Phone</p>
                  <a 
                    href="tel:{phoneNumber}" 
                    class="text-sm hover:underline break-all"
                  >
                    {phoneNumber}
                  </a>
                </div>
              </div>
            {/if}
            
            {#if contact.company}
              <div class="flex items-start gap-3">
                <Building class="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-muted-foreground">Company</p>
                  <p class="text-sm break-all">{contact.company}</p>
                </div>
              </div>
            {/if}
            
            {#if contact.city || contact.country}
              <div class="flex items-start gap-3">
                <MapPin class="h-4 w-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-muted-foreground">Location</p>
                  <p class="text-sm break-all">
                    {[contact.city, contact.country].filter(Boolean).join(', ')}
                  </p>
                </div>
              </div>
            {/if}
          </Card.Content>
        </Card.Root>
        
        <!-- Custom Attributes -->
        {#if Object.keys(customAttributes).length > 0}
          <Card.Root>
            <Card.Header>
              <Card.Title class="text-sm">Custom Attributes</Card.Title>
            </Card.Header>
            <Card.Content>
              <div class="space-y-2">
                {#each Object.entries(customAttributes) as [key, value]}
                  <div class="py-2 border-b last:border-b-0">
                    <p class="text-xs text-muted-foreground mb-1 capitalize">
                      {key.replace(/_/g, ' ')}
                    </p>
                    <p class="text-sm break-all">{value}</p>
                  </div>
                {/each}
              </div>
            </Card.Content>
          </Card.Root>
        {/if}
        
        <!-- Social Profiles -->
        {#if contact.socialProfiles && Object.keys(contact.socialProfiles).length > 0}
          <Card.Root>
            <Card.Header>
              <Card.Title class="text-sm">Social Profiles</Card.Title>
            </Card.Header>
            <Card.Content class="space-y-2">
              {#each contact.socialProfiles as profile}
                {#if profile.url}
                  <a
                    href={profile.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-2 text-sm hover:underline"
                  >
                    <span class="capitalize">{profile.type}</span>
                    <span class="text-muted-foreground">→</span>
                  </a>
                {/if}
              {/each}
            </Card.Content>
          </Card.Root>
        {/if}
        
        <!-- Actions -->
        <div class="flex gap-2">
          <Button variant="outline" class="flex-1" size="sm">
            Edit Contact
          </Button>
          <Button variant="outline" class="flex-1" size="sm">
            View History
          </Button>
        </div>
      </div>
    </div>
  {:else}
    <!-- Loading or Not Found -->
    <div class="flex items-center justify-center h-full">
      <p class="text-muted-foreground">Contact not found</p>
    </div>
  {/if}
</div>
