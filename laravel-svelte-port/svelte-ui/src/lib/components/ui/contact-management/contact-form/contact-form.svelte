<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Avatar } from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '$lib/components/ui/select';

  interface Contact {
    id?: string;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
    company?: string;
    tags?: string[];
    status?: 'active' | 'inactive';
  }

  let { contact = null } = $props<{ contact?: Contact | null }>();

  const dispatch = createEventDispatcher();

  let form: Contact = {
    name: '',
    email: '',
    phone: '',
    company: '',
    tags: [],
    status: 'active'
  };

  let avatarFile = $state<File | null>(null);
  let avatarPreview = $state<string | null>(null);
  let errors = $state<Record<string, string>>({});

  $effect(() => {
    if (contact) {
      form = { ...contact };
    }
    avatarPreview = form.avatar || null;
  });

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
    if (!form.name || form.name.trim().length === 0) {
      errors.name = 'Name is required';
    }
    if (!form.email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(form.email)) {
      errors.email = 'A valid email is required';
    }
    return Object.keys(errors).length === 0;
  }

  function save() {
    if (!validate()) return;
    const payload = { ...form } as Contact;
    if (avatarFile) {
      // provide file for upload handling by parent
      (payload as any)._avatarFile = avatarFile;
    }
    dispatch('save', payload);
  }

  function cancel() {
    dispatch('cancel');
  }
</script>

<div class="space-y-4 p-4 max-w-2xl">
  <div class="flex items-center gap-4">
    <Avatar class="h-14 w-14">
      {#if avatarPreview}
        <img src={avatarPreview} alt={form.name || 'avatar'} />
      {:else}
        <div class="flex h-full w-full items-center justify-center bg-primary text-primary-foreground">
          {form.name ? form.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2) : '??'}
        </div>
      {/if}
    </Avatar>
    <div class="flex flex-col flex-1">
      <label class="text-sm text-muted-foreground" for="contact-avatar">Upload Avatar</label>
      <input id="contact-avatar" type="file" accept="image/*" onchange={(e: Event) => onFileChange(e)} />
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="text-sm" for="contact-name">Name</label>
      <Input id="contact-name" bind:value={form.name} placeholder="Full name" />
      {#if errors.name}
        <p class="text-xs text-destructive">{errors.name}</p>
      {/if}
    </div>

    <div>
      <label class="text-sm" for="contact-email">Email</label>
      <Input id="contact-email" bind:value={form.email} placeholder="name@example.com" type="email" />
      {#if errors.email}
        <p class="text-xs text-destructive">{errors.email}</p>
      {/if}
    </div>

    <div>
      <label class="text-sm" for="contact-phone">Phone</label>
      <Input id="contact-phone" bind:value={form.phone} placeholder="(555) 555-5555" />
    </div>

    <div>
      <label class="text-sm" for="contact-company">Company</label>
      <Input id="contact-company" bind:value={form.company} placeholder="Company name" />
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="text-sm" for="contact-tags">Tags (comma separated)</label>
      <Input
        id="contact-tags"
        value={form.tags?.join(', ')}
        oninput={(e: Event) =>
          (form.tags = (e.target as HTMLInputElement).value
            .split(',')
            .map((s: string) => s.trim())
            .filter(Boolean))}
      />
    </div>

    <div>
      <label class="text-sm" for="contact-status">Status</label>
      <Select bind:value={form.status}>
        <SelectTrigger class="w-full" id="contact-status" aria-label="Status">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="active">Active</SelectItem>
          <SelectItem value="inactive">Inactive</SelectItem>
        </SelectContent>
      </Select>
    </div>
  </div>

  <div class="flex items-center justify-end gap-2">
    <Button variant="ghost" onclick={(e: MouseEvent) => cancel()}>Cancel</Button>
    <Button onclick={(e: MouseEvent) => save()}>Save</Button>
  </div>
</div>
