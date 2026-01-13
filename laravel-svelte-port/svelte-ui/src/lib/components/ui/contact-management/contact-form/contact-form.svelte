<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Avatar } from '../../../avatar/index.js';
  import { Button } from '../../../button/index.js';
  import { Input } from '../../../input/index.js';
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../select/index.js';

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

  let form: Contact = contact
    ? { ...contact }
    : { name: '', email: '', phone: '', company: '', tags: [], status: 'active' };

  let avatarFile: File | null = null;
  let avatarPreview: string | null = form.avatar || null;
  let errors: Record<string, string> = {};

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
      <label class="text-sm text-muted-foreground">Upload Avatar</label>
      <input type="file" accept="image/*" on:change={(e: Event) => onFileChange(e)} />
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="text-sm">Name</label>
      <Input bind:value={form.name} placeholder="Full name" />
      {#if errors.name}
        <p class="text-xs text-destructive">{errors.name}</p>
      {/if}
    </div>

    <div>
      <label class="text-sm">Email</label>
      <Input bind:value={form.email} placeholder="name@example.com" type="email" />
      {#if errors.email}
        <p class="text-xs text-destructive">{errors.email}</p>
      {/if}
    </div>

    <div>
      <label class="text-sm">Phone</label>
      <Input bind:value={form.phone} placeholder="(555) 555-5555" />
    </div>

    <div>
      <label class="text-sm">Company</label>
      <Input bind:value={form.company} placeholder="Company name" />
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="text-sm">Tags (comma separated)</label>
      <Input value={form.tags?.join(', ')} on:input={(e: Event & { target: HTMLInputElement }) => (form.tags = (e.target as HTMLInputElement).value.split(',').map(s => s.trim()).filter(Boolean))} />
    </div>

    <div>
      <label class="text-sm">Status</label>
      <Select bind:value={form.status}>
        <SelectTrigger class="w-full">
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
    <Button variant="ghost" on:click={(e: MouseEvent) => cancel()}>Cancel</Button>
    <Button on:click={(e: MouseEvent) => save()}>Save</Button>
  </div>
</div>