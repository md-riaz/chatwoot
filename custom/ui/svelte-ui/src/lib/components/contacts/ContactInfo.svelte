<script lang="ts">
  /**
   * ContactInfo - Compact contact information display
   * For use in headers or inline displays
   */
  
  import * as Avatar from '$lib/components/ui/avatar';
  import { Badge } from '$lib/components/ui/badge';
  import type { Contact } from '$lib/api/contacts';
  
  interface Props {
    contact: Contact;
    showStatus?: boolean;
    size?: 'sm' | 'md' | 'lg';
  }
  
  let { contact, showStatus = true, size = 'md' }: Props = $props();
  
  const avatarSizes = {
    sm: 'h-8 w-8',
    md: 'h-10 w-10',
    lg: 'h-12 w-12',
  };
  
  const displayName = $derived(contact.name || 'Unknown');
  const avatarUrl = $derived(contact.thumbnail || '');
  const status = $derived(contact.availabilityStatus);
</script>

<div class="flex items-center gap-3">
  <Avatar.Root class={avatarSizes[size]}>
    <Avatar.Image src={avatarUrl} alt={displayName} />
    <Avatar.Fallback>
      {displayName.charAt(0).toUpperCase()}
    </Avatar.Fallback>
  </Avatar.Root>
  
  <div class="flex-1 min-w-0">
    <p class="font-medium truncate">{displayName}</p>
    {#if showStatus && status}
      <Badge variant="secondary" class="text-xs mt-1">
        {status}
      </Badge>
    {/if}
  </div>
</div>
