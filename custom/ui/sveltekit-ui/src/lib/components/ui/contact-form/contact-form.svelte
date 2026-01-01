<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';

  interface Props {
    contact?: {
      name?: string;
      email?: string;
      phone?: string;
      company?: string;
      location?: string;
      bio?: string;
    };
    onSubmit?: (contact: Record<string, string>) => void;
    onCancel?: () => void;
    class?: string;
  }

  let { contact = {}, onSubmit, onCancel, class: className }: Props = $props();
  
  let name = $state(contact.name || '');
  let email = $state(contact.email || '');
  let phone = $state(contact.phone || '');
  let company = $state(contact.company || '');
  let location = $state(contact.location || '');
  let bio = $state(contact.bio || '');

  function handleSubmit(e: Event) {
    e.preventDefault();
    onSubmit?.({ name, email, phone, company, location, bio });
  }
</script>

<form class={cn('space-y-4', className)} onsubmit={handleSubmit}>
  <div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
      <Label for="name">Name</Label>
      <Input id="name" bind:value={name} placeholder="Enter name" />
    </div>
    <div>
      <Label for="email">Email</Label>
      <Input id="email" type="email" bind:value={email} placeholder="email@example.com" />
    </div>
    <div>
      <Label for="phone">Phone</Label>
      <Input id="phone" type="tel" bind:value={phone} placeholder="+1 234 567 8900" />
    </div>
    <div>
      <Label for="company">Company</Label>
      <Input id="company" bind:value={company} placeholder="Company name" />
    </div>
    <div>
      <Label for="location">Location</Label>
      <Input id="location" bind:value={location} placeholder="City, Country" />
    </div>
    <div class="col-span-2">
      <Label for="bio">Bio</Label>
      <Textarea id="bio" bind:value={bio} placeholder="Brief description..." rows={3} />
    </div>
  </div>
  
  <div class="flex justify-end gap-2">
    <Button type="button" variant="outline" onclick={onCancel}>Cancel</Button>
    <Button type="submit">Save Contact</Button>
  </div>
</form>
