<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Avatar } from '$lib/components/ui/avatar';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { Card } from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { ContactForm } from '../contact-form/index.js';
  import {
    Dialog as DialogRoot,
    Content as DialogContent,
    Header as DialogHeader,
    Title as DialogTitle,
    Description as DialogDescription,
    Footer as DialogFooter,
    Close as DialogClose,
  } from '../../../dialog/index.js';

  interface Activity {
    id: string;
    type: string;
    text: string;
    date: string;
  }

  interface Note {
    id: string;
    author: string;
    body: string;
    date: string;
  }

  interface Contact {
    id: string;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
    company?: string;
    tags?: string[];
    status?: 'active' | 'inactive';
    createdAt?: string;
    lastActivity?: string;
    attributes?: Record<string, string>;
    timeline?: Activity[];
    notes?: Note[];
  }

  let { contact = null } = $props<{ contact?: Contact | null }>();

  const dispatch = createEventDispatcher();

  let newNote = '';
  let localNotes: Note[] = contact?.notes ? [...contact.notes] : [];
  let showEditor = false;

  // Use $effect instead of $:
  $effect(() => {
    localNotes = contact?.notes ? [...contact.notes] : localNotes;
  });

  function goBack() {
    dispatch('back');
  }

  function edit() {
    // kept for compatibility: open editor via programmatic call
    showEditor = true;
  }

  $effect(() => {
    if (showEditor) {
      dispatch('edit', contact);
    }
  });

  function addNote() {
    if (!newNote.trim()) return;
    const note: Note = {
      id: String(Date.now()),
      author: 'You',
      body: newNote.trim(),
      date: new Date().toISOString(),
    };
    localNotes = [note, ...localNotes];
    newNote = '';
    dispatch('addNote', note);
  }
</script>

<DialogRoot bind:open={showEditor}>
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Edit Contact</DialogTitle>
      <DialogDescription>Update contact details for this contact.</DialogDescription>
      <DialogClose class="absolute right-3 top-3">Close</DialogClose>
    </DialogHeader>

    <div class="p-2">
      <ContactForm contact={contact} on:save={(e: CustomEvent<{ detail: any }>) => { dispatch('save', e.detail); showEditor = false; }} on:cancel={() => (showEditor = false)} />
    </div>

    <DialogFooter>
      <DialogClose class="px-3 py-1">Close</DialogClose>
    </DialogFooter>
  </DialogContent>
</DialogRoot>

{#if !contact}
  <div class="p-6 text-muted-foreground">No contact selected</div>
{:else}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-4">
    <!-- Profile Card -->
    <Card class="p-4">
      <div class="flex flex-col items-center text-center gap-3">
        <Avatar class="h-20 w-20">
          {#if contact.avatar}
            <img src={contact.avatar} alt={contact.name} />
          {:else}
            <div class="flex h-full w-full items-center justify-center bg-primary text-primary-foreground">
              {contact.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0,2)}
            </div>
          {/if}
        </Avatar>
        <div class="text-lg font-medium">{contact.name}</div>
        <div class="text-sm text-muted-foreground">{contact.company || '-'}</div>
        <div class="flex items-center gap-2 mt-2">
          {#if contact.tags}
            {#each contact.tags as t}
              <Badge class="text-xs">{t}</Badge>
            {/each}
          {/if}
        </div>
        <div class="flex gap-2 mt-4">
          <Button variant="outline" size="sm" onclick={(e: MouseEvent) => goBack()}>Back</Button>
          <Button size="sm" onclick={(e: MouseEvent) => edit()}>Edit</Button>
        </div>
      </div>
    </Card>

    <!-- Details & Timeline -->
    <div class="lg:col-span-2 space-y-4">
      <Card class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="text-xs text-muted-foreground">Email</div>
            <div class="font-medium">{contact.email}</div>
          </div>
          <div>
            <div class="text-xs text-muted-foreground">Phone</div>
            <div class="font-medium">{contact.phone || '-'}</div>
          </div>
          <div>
            <div class="text-xs text-muted-foreground">Status</div>
            <div class="font-medium">{contact.status || 'active'}</div>
          </div>
        </div>
      </Card>

      <Card class="p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="font-medium">Activity Timeline</div>
        </div>
        {#if contact.timeline && contact.timeline.length > 0}
          <div class="space-y-3">
            {#each contact.timeline as item}
              <div class="flex items-start gap-3">
                <div class="w-36 text-xs text-muted-foreground">{new Date(item.date).toLocaleString()}</div>
                <div class="flex-1">
                  <div class="text-sm font-medium">{item.type}</div>
                  <div class="text-sm text-muted-foreground">{item.text}</div>
                </div>
              </div>
            {/each}
          </div>
        {:else}
          <div class="text-sm text-muted-foreground">No recent activity</div>
        {/if}
      </Card>

      <Card class="p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="font-medium">Notes</div>
        </div>
        <div class="space-y-3">
          <div class="flex gap-2">
            <Input bind:value={newNote} placeholder="Write a note..." />
            <Button onclick={(e: MouseEvent) => addNote()}>Add</Button>
          </div>

          {#if localNotes.length === 0}
            <div class="text-sm text-muted-foreground">No notes yet</div>
          {:else}
            <div class="space-y-3">
              {#each localNotes as n}
                <div class="border rounded p-3">
                  <div class="flex items-center justify-between">
                    <div class="text-sm font-medium">{n.author}</div>
                    <div class="text-xs text-muted-foreground">{new Date(n.date).toLocaleString()}</div>
                  </div>
                  <div class="text-sm mt-2">{n.body}</div>
                </div>
              {/each}
            </div>
          {/if}
        </div>
      </Card>
    </div>
  </div>
{/if}