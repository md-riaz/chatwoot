<script lang="ts">
  /**
   * Contact Detail Page
   * View and manage individual contact with sidebar tabs
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import MergeContactDialog from '$lib/components/ui/contact-management/merge-contact-dialog.svelte';
  import {
    ArrowLeft,
    Mail,
    Phone,
    Building,
    MapPin,
    Calendar,
    Clock,
    MessageCircle,
    PhoneCall,
    Ban,
    MoreVertical,
    Pencil,
    Trash2,
    Camera,
    Upload,
    PanelRightOpen,
    Check,
    Merge,
  } from 'lucide-svelte';

  // UI Components
  import { Button, buttonVariants } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import * as Dialog from '$lib/components/ui/dialog';
  import * as Sheet from '$lib/components/ui/sheet';
  import * as Tabs from '$lib/components/ui/tabs';
  import * as Card from '$lib/components/ui/card';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as Skeleton from '$lib/components/ui/skeleton';
  import { Input } from '$lib/components/ui/input';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Badge } from '$lib/components/ui/badge';

  // Specific Components
  import ContactForm from '$lib/components/ui/contact-management/contact-form/contact-form.svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';

  // ... state ...
  let showMergeDialog = $state(false);

  const accountId = $derived(parseInt($page.params.accountId ?? '', 10));
  const contactId = $derived(parseInt($page.params.contactId ?? '', 10));

  // Reactive store access
  const isLoading = $derived(contactsStore.isLoading);
  const contact = $derived(
    contactsStore.allContacts.find(c => c.id === contactId) || null
  );

  // Local state
  let activeTab = $state<'attributes' | 'history' | 'notes' | 'merge'>(
    'attributes'
  );
  let showEditDialog = $state(false);
  let showDeleteDialog = $state(false);
  let showMobileSidebar = $state(false);
  let isUpdating = $state(false);
  let isDeleting = $state(false);
  let isUploadingAvatar = $state(false);
  let isBlocking = $state(false);
  let newNote = $state('');
  let avatarInput = $state<HTMLInputElement>();

  // Navigate back to contacts list
  function goBack() {
    goto(`/app/accounts/${accountId}/contacts`);
  }

  // Handle contact update
  async function handleUpdateContact(event: CustomEvent<any>) {
    const contactData = event.detail;
    const avatarFile = contactData.avatar;
    const { avatar, ...apiData } = contactData;

    try {
      isUpdating = true;
      const updated = await contactsStore.updateContact(contactId, apiData);

      if (updated && avatarFile) {
        await contactsStore.updateContact(contactId, { avatar: avatarFile });
      }
      showEditDialog = false;
    } catch (error) {
      console.error('Failed to update contact', error);
    } finally {
      isUpdating = false;
    }
  }

  // Handle contact delete
  async function handleDeleteContact() {
    try {
      isDeleting = true;
      const success = await contactsStore.deleteContact(contactId);
      if (success) {
        goBack();
      }
    } catch (error) {
      console.error('Failed to delete contact', error);
    } finally {
      isDeleting = false;
      showDeleteDialog = false;
    }
  }

  // Handle avatar upload
  async function handleAvatarUpload(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    try {
      isUploadingAvatar = true;
      await contactsStore.updateContact(contactId, { avatar: file });
    } catch (error) {
      console.error('Failed to upload avatar', error);
    } finally {
      isUploadingAvatar = false;
      // Reset input
      if (avatarInput) avatarInput.value = '';
    }
  }

  // Handle avatar delete
  async function handleDeleteAvatar() {
    try {
      isUploadingAvatar = true;
      await contactsStore.deleteContactAvatar(contactId);
    } catch (error) {
      console.error('Failed to delete avatar', error);
    } finally {
      isUploadingAvatar = false;
    }
  }

  // Handle block/unblock contact (placeholder - API not yet implemented)
  async function handleToggleBlock() {
    try {
      isBlocking = true;
      // TODO: Call blockContact/unblockContact API when available
      console.log('Block/unblock contact:', contactId);
      await new Promise(resolve => setTimeout(resolve, 500)); // Simulate API call
    } catch (error) {
      console.error('Failed to toggle block status', error);
    } finally {
      isBlocking = false;
    }
  }

  // Format date
  function formatDate(dateString: string | null | undefined): string {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  }

  // Format relative time
  function formatRelativeTime(dateString: string | null | undefined): string {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    return formatDate(dateString);
  }

  // Load contact on mount
  onMount(async () => {
    if (contactId) {
      await contactsStore.fetchContact(contactId);
      contactsStore.selectContact(contactId);
    }
  });
</script>

<div class="h-full flex flex-col bg-background">
  <!-- Header with breadcrumb and actions -->
  <div class="flex items-center justify-between px-6 py-4 border-b">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" onclick={goBack}>
        <ArrowLeft class="h-4 w-4" />
      </Button>
      <div class="flex items-center gap-2 text-sm">
        <button
          class="text-muted-foreground hover:text-foreground"
          onclick={goBack}
        >
          Contacts
        </button>
        <span class="text-muted-foreground">/</span>
        <span class="font-medium">{contact?.name || 'Contact'}</span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <!-- Action buttons -->
      <Button variant="outline" size="sm" class="gap-2">
        <MessageCircle class="h-4 w-4" />
        Send Message
      </Button>
      <Button variant="outline" size="sm" class="gap-2">
        <PhoneCall class="h-4 w-4" />
        Call
      </Button>

      <!-- More actions -->
      <DropdownMenu.Root>
        <DropdownMenu.Trigger
          class={buttonVariants({ variant: 'ghost', size: 'icon' })}
        >
          <MoreVertical class="h-4 w-4" />
        </DropdownMenu.Trigger>
        <DropdownMenu.Content align="end">
          <DropdownMenu.Item onclick={() => (showEditDialog = true)}>
            <Pencil class="h-4 w-4 mr-2" />
            Edit Contact
          </DropdownMenu.Item>
          <DropdownMenu.Item onclick={handleToggleBlock} disabled={isBlocking}>
            <Ban class="h-4 w-4 mr-2" />
            {isBlocking ? 'Blocking...' : 'Block Contact'}
          </DropdownMenu.Item>
          <DropdownMenu.Separator />
          <DropdownMenu.Item
            class="text-destructive"
            onclick={() => (showDeleteDialog = true)}
          >
            <Trash2 class="h-4 w-4 mr-2" />
            Delete Contact
          </DropdownMenu.Item>
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  </div>

  <!-- Main content with sidebar -->
  <div class="flex-1 flex overflow-hidden">
    {#if isLoading && !contact}
      <!-- Loading state -->
      <div class="flex-1 p-6 space-y-4">
        <div class="flex items-start gap-4">
          <Skeleton.Root class="h-20 w-20 rounded-full" />
          <div class="space-y-3">
            <Skeleton.Root class="h-6 w-48" />
            <Skeleton.Root class="h-4 w-32" />
            <Skeleton.Root class="h-4 w-40" />
          </div>
        </div>
        <Skeleton.Root class="h-32 w-full" />
        <Skeleton.Root class="h-48 w-full" />
      </div>
    {:else if !contact}
      <!-- Contact not found -->
      <div class="flex-1 flex items-center justify-center">
        <div class="text-center">
          <h2 class="text-xl font-medium mb-2">Contact not found</h2>
          <p class="text-muted-foreground mb-4">
            The contact you're looking for doesn't exist.
          </p>
          <Button onclick={goBack}>Back to Contacts</Button>
        </div>
      </div>
    {:else}
      <!-- Main content area -->
      <main class="flex-1 overflow-y-auto p-6">
        <!-- Contact header -->
        <div class="flex items-start gap-6 mb-6">
          <!-- Avatar with upload overlay -->
          <div class="relative group">
            <input
              type="file"
              accept="image/*"
              class="hidden"
              bind:this={avatarInput}
              onchange={handleAvatarUpload}
            />
            <button
              type="button"
              onclick={() => avatarInput?.click()}
              class="block focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 rounded-full"
              disabled={isUploadingAvatar}
            >
              <Avatar.Root class="h-20 w-20">
                <Avatar.Image
                  src={contact.thumbnail || contact.avatarUrl || ''}
                  alt={contact.name}
                />
                <Avatar.Fallback class="text-xl">
                  {contact.name?.charAt(0).toUpperCase() || '?'}
                </Avatar.Fallback>
              </Avatar.Root>
              <!-- Camera overlay on hover -->
              <div
                class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
              >
                {#if isUploadingAvatar}
                  <div
                    class="animate-spin h-6 w-6 border-2 border-white border-t-transparent rounded-full"
                  ></div>
                {:else}
                  <Camera class="h-6 w-6 text-white" />
                {/if}
              </div>
            </button>
            <!-- Delete avatar button (show only if has avatar) -->
            {#if contact.thumbnail || contact.avatarUrl}
              <button
                type="button"
                onclick={handleDeleteAvatar}
                disabled={isUploadingAvatar}
                class="absolute -bottom-1 -right-1 h-6 w-6 bg-destructive text-destructive-foreground rounded-full flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity hover:bg-destructive/90"
                title="Remove avatar"
              >
                ×
              </button>
            {/if}
          </div>

          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-2xl font-semibold">
                {contact.name || 'Unknown Contact'}
              </h1>
              {#if contact.availabilityStatus === 'online'}
                <Badge variant="default" class="bg-green-500">Online</Badge>
              {:else if contact.availabilityStatus}
                <Badge variant="secondary">{contact.availabilityStatus}</Badge>
              {/if}
            </div>

            {#if contact.identifier}
              <p class="text-sm text-muted-foreground mb-3">
                ID: {contact.identifier}
              </p>
            {/if}

            <div
              class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground"
            >
              {#if contact.email}
                <div class="flex items-center gap-1">
                  <Mail class="h-4 w-4" />
                  <span>{contact.email}</span>
                </div>
              {/if}
              {#if contact.phoneNumber}
                <div class="flex items-center gap-1">
                  <Phone class="h-4 w-4" />
                  <span>{contact.phoneNumber}</span>
                </div>
              {/if}
              {#if contact.company}
                <div class="flex items-center gap-1">
                  <Building class="h-4 w-4" />
                  <span>{contact.company}</span>
                </div>
              {/if}
              {#if contact.city || contact.country}
                <div class="flex items-center gap-1">
                  <MapPin class="h-4 w-4" />
                  <span
                    >{[contact.city, contact.country]
                      .filter(Boolean)
                      .join(', ')}</span
                  >
                </div>
              {/if}
            </div>

            <div
              class="flex items-center gap-6 mt-4 text-sm text-muted-foreground"
            >
              <div class="flex items-center gap-1">
                <Calendar class="h-4 w-4" />
                <span>Created {formatDate(contact.createdAt)}</span>
              </div>
              <div class="flex items-center gap-1">
                <Clock class="h-4 w-4" />
                <span
                  >Last activity {formatRelativeTime(
                    contact.lastActivityAt
                  )}</span
                >
              </div>
            </div>
          </div>

          <Button onclick={() => (showEditDialog = true)}>
            <Pencil class="h-4 w-4 mr-2" />
            Edit
          </Button>
        </div>

        <!-- Contact details form -->
        <Card.Root class="mb-6">
          <Card.Header>
            <Card.Title>Contact Information</Card.Title>
          </Card.Header>
          <Card.Content>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-sm font-medium text-muted-foreground">
                  Email
                </div>
                <p class="mt-1">{contact.email || '-'}</p>
              </div>
              <div>
                <div class="text-sm font-medium text-muted-foreground">
                  Phone
                </div>
                <p class="mt-1">{contact.phoneNumber || '-'}</p>
              </div>
              <div>
                <div class="text-sm font-medium text-muted-foreground">
                  Company
                </div>
                <p class="mt-1">{contact.company || '-'}</p>
              </div>
              <div>
                <div class="text-sm font-medium text-muted-foreground">
                  Location
                </div>
                <p class="mt-1">
                  {[contact.city, contact.country].filter(Boolean).join(', ') ||
                    '-'}
                </p>
              </div>
              <div>
                <div class="text-sm font-medium text-muted-foreground">
                  Identifier
                </div>
                <p class="mt-1">{contact.identifier || '-'}</p>
              </div>
              <div>
                <div class="text-sm font-medium text-muted-foreground">
                  Conversations
                </div>
                <p class="mt-1">{contact.conversationsCount || 0}</p>
              </div>
            </div>
          </Card.Content>
        </Card.Root>

        <!-- Custom Attributes -->
        {#if contact.customAttributes && Object.keys(contact.customAttributes).length > 0}
          <Card.Root class="mb-6">
            <Card.Header>
              <Card.Title>Custom Attributes</Card.Title>
            </Card.Header>
            <Card.Content>
              <div class="grid grid-cols-2 gap-4">
                {#each Object.entries(contact.customAttributes) as [key, value]}
                  <div>
                    <div class="text-sm font-medium text-muted-foreground">
                      {key}
                    </div>
                    <p class="mt-1">{value}</p>
                  </div>
                {/each}
              </div>
            </Card.Content>
          </Card.Root>
        {/if}
      </main>

      <!-- Desktop Sidebar with tabs (hidden on mobile) -->
      <aside class="hidden lg:block w-80 border-l bg-muted/30 overflow-y-auto">
        <Tabs.Root bind:value={activeTab} class="h-full flex flex-col">
          <Tabs.List
            class="grid w-full grid-cols-4 border-b px-2 pt-2 bg-background"
          >
            <Tabs.Trigger value="attributes">Attrs</Tabs.Trigger>
            <Tabs.Trigger value="history">History</Tabs.Trigger>
            <Tabs.Trigger value="notes">Notes</Tabs.Trigger>
            <Tabs.Trigger value="merge">Merge</Tabs.Trigger>
          </Tabs.List>

          <div class="flex-1 overflow-y-auto p-4">
            <Tabs.Content value="attributes" class="mt-0">
              <h3 class="font-medium mb-3">Additional Attributes</h3>
              {#if contact.additionalAttributes && Object.keys(contact.additionalAttributes).length > 0}
                <div class="space-y-3">
                  {#each Object.entries(contact.additionalAttributes) as [key, value]}
                    <div class="text-sm">
                      <span class="text-muted-foreground">{key}:</span>
                      <span class="ml-2">{value}</span>
                    </div>
                  {/each}
                </div>
              {:else}
                <p class="text-sm text-muted-foreground">
                  No additional attributes
                </p>
              {/if}
            </Tabs.Content>

            <Tabs.Content value="history" class="mt-0">
              <h3 class="font-medium mb-3">Conversation History</h3>
              <p class="text-sm text-muted-foreground">
                {contact.conversationsCount || 0} conversations
              </p>
              <!-- TODO: Load and display conversation history -->
            </Tabs.Content>

            <Tabs.Content value="notes" class="mt-0">
              <h3 class="font-medium mb-3">Notes</h3>
              <div class="space-y-3">
                <div class="flex gap-2">
                  <Textarea
                    bind:value={newNote}
                    placeholder="Add a note..."
                    rows={2}
                  />
                </div>
                <Button size="sm" disabled={!newNote.trim()}>Add Note</Button>
              </div>
              <p class="text-sm text-muted-foreground mt-4">No notes yet</p>
            </Tabs.Content>

            <Tabs.Content value="merge" class="mt-0">
              <h3 class="font-medium mb-3">Merge Contact</h3>
              <p class="text-sm text-muted-foreground mb-4">
                Search for a contact to merge with this one. The current contact
                will be kept.
              </p>
              <Input placeholder="Search contacts to merge..." />
              <!-- TODO: Implement merge search and action -->
            </Tabs.Content>
          </div>
        </Tabs.Root>
      </aside>

      <!-- Mobile sidebar toggle button (visible on mobile only) -->
      <Button
        variant="outline"
        size="icon"
        class="fixed bottom-4 right-4 lg:hidden z-50 h-12 w-12 rounded-full shadow-lg"
        onclick={() => (showMobileSidebar = true)}
      >
        <PanelRightOpen class="h-5 w-5" />
      </Button>

      <!-- Mobile Sidebar Sheet -->
      <Sheet.Root bind:open={showMobileSidebar}>
        <Sheet.Content side="right" class="w-80 p-0">
          <Sheet.Header class="px-4 py-3 border-b">
            <Sheet.Title>Contact Details</Sheet.Title>
          </Sheet.Header>
          <Tabs.Root
            bind:value={activeTab}
            class="flex flex-col h-[calc(100%-60px)]"
          >
            <Tabs.List class="grid w-full grid-cols-4 border-b px-2 pt-2">
              <Tabs.Trigger value="attributes">Attrs</Tabs.Trigger>
              <Tabs.Trigger value="history">History</Tabs.Trigger>
              <Tabs.Trigger value="notes">Notes</Tabs.Trigger>
              <Tabs.Trigger value="merge">Merge</Tabs.Trigger>
            </Tabs.List>

            <div class="flex-1 overflow-y-auto p-4">
              <Tabs.Content value="attributes" class="mt-0">
                <h3 class="font-medium mb-3">Additional Attributes</h3>
                {#if contact.additionalAttributes && Object.keys(contact.additionalAttributes).length > 0}
                  <div class="space-y-3">
                    {#each Object.entries(contact.additionalAttributes) as [key, value]}
                      <div class="text-sm">
                        <span class="text-muted-foreground">{key}:</span>
                        <span class="ml-2">{value}</span>
                      </div>
                    {/each}
                  </div>
                {:else}
                  <p class="text-sm text-muted-foreground">
                    No additional attributes
                  </p>
                {/if}
              </Tabs.Content>

              <Tabs.Content value="history" class="mt-0">
                <h3 class="font-medium mb-3">Conversation History</h3>
                <p class="text-sm text-muted-foreground">
                  {contact.conversationsCount || 0} conversations
                </p>
              </Tabs.Content>

              <Tabs.Content value="notes" class="mt-0">
                <h3 class="font-medium mb-3">Notes</h3>
                <div class="space-y-3">
                  <Textarea
                    bind:value={newNote}
                    placeholder="Add a note..."
                    rows={2}
                  />
                  <Button size="sm" disabled={!newNote.trim()}>Add Note</Button>
                </div>
                <p class="text-sm text-muted-foreground mt-4">No notes yet</p>
              </Tabs.Content>

              <Tabs.Content value="merge" class="mt-0">
                <h3 class="font-medium mb-3">Merge Contact</h3>
                <p class="text-sm text-muted-foreground mb-4">
                  Search for a contact to merge.
                </p>
                <Input placeholder="Search contacts..." />
              </Tabs.Content>
            </div>
          </Tabs.Root>
        </Sheet.Content>
      </Sheet.Root>
    {/if}
  </div>

  <!-- Edit Contact Dialog -->
  <Dialog.Root bind:open={showEditDialog}>
    <Dialog.Content class="sm:max-w-[600px]">
      <Dialog.Header>
        <Dialog.Title>Edit Contact</Dialog.Title>
      </Dialog.Header>
      {#if contact}
        <ContactForm
          {contact}
          on:save={handleUpdateContact}
          on:cancel={() => (showEditDialog = false)}
          serverErrors={contactsStore.validationErrors}
        />
      {/if}
    </Dialog.Content>
  </Dialog.Root>

  <!-- Delete Confirmation Dialog -->
  <Dialog.Root bind:open={showDeleteDialog}>
    <Dialog.Content class="sm:max-w-[400px]">
      <Dialog.Header>
        <Dialog.Title>Delete Contact</Dialog.Title>
        <Dialog.Description>
          Are you sure you want to delete "{contact?.name}"? This action cannot
          be undone.
        </Dialog.Description>
      </Dialog.Header>
      <Dialog.Footer class="gap-2">
        <Button variant="outline" onclick={() => (showDeleteDialog = false)}
          >Cancel</Button
        >
        <Button
          variant="destructive"
          disabled={isDeleting}
          onclick={handleDeleteContact}
        >
          {isDeleting ? 'Deleting...' : 'Delete'}
        </Button>
      </Dialog.Footer>
    </Dialog.Content>
  </Dialog.Root>
</div>
