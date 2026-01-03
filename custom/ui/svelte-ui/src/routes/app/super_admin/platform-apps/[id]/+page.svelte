<script lang="ts">
import { onMount } from 'svelte';
import { goto } from '$app/navigation';
import { page } from '$app/stores';
import { toast } from 'svelte-sonner';
import { api } from '$lib/api/superAdmin';
import Button from '$lib/components/ui/button/button.svelte';
import Input from '$lib/components/ui/input/input.svelte';
import Label from '$lib/components/ui/label/label.svelte';
import { Skeleton } from '$lib/components/ui/skeleton';
import * as Dialog from '$lib/components/ui/dialog';
import { RefreshCw, Trash2, Copy } from 'lucide-svelte';

const id = $page.params.id;

let loading = true;
let saving = false;
let platformApp: any = null;
let showRegenerateDialog = false;
let showDeleteDialog = false;
let showTokenDialog = false;
let newToken = '';

let formData = {
name: '',
webhook_url: ''
};

onMount(async () => {
await loadPlatformApp();
});

async function loadPlatformApp() {
try {
loading = true;
platformApp = await api.platformApps.get(id);
formData = {
name: platformApp.name || '',
webhook_url: platformApp.webhook_url || ''
};
} catch (error: any) {
toast.error(error.message || 'Failed to load platform app');
} finally {
loading = false;
}
}

async function handleSave() {
try {
saving = true;
await api.platformApps.update(id, formData);
toast.success('Platform app updated successfully');
await loadPlatformApp();
} catch (error: any) {
toast.error(error.message || 'Failed to update platform app');
} finally {
saving = false;
}
}

async function handleDelete() {
try {
await api.platformApps.delete(id);
toast.success('Platform app deleted successfully');
goto('/app/super_admin/platform-apps');
} catch (error: any) {
toast.error(error.message || 'Failed to delete platform app');
}
showDeleteDialog = false;
}

async function handleRegenerateToken() {
try {
const response = await api.platformApps.regenerateToken(id);
newToken = response.token;
showRegenerateDialog = false;
showTokenDialog = true;
await loadPlatformApp();
} catch (error: any) {
toast.error(error.message || 'Failed to regenerate token');
}
}

async function copyToken() {
try {
await navigator.clipboard.writeText(newToken);
toast.success('Token copied to clipboard');
} catch (error) {
toast.error('Failed to copy token');
}
}

function maskToken(token: string) {
if (!token || token.length < 8) return '••••••••';
return '••••••••' + token.slice(-8);
}
</script>

<div class="flex-1 bg-white dark:bg-slate-1">
<!-- Header -->
<div class="border-b border-slate-6 px-8 py-6">
<div class="flex items-center justify-between">
<div>
<h1 class="text-2xl font-semibold text-slate-12">Platform App Details</h1>
<p class="mt-1 text-sm text-slate-11">
<a href="/app/super_admin/platform-apps" class="hover:text-iris-9">Platform Apps</a>
/ {platformApp?.name || 'Loading...'}
</p>
</div>
</div>
</div>

<!-- Content -->
<div class="p-8">
{#if loading}
<div class="space-y-6">
<Skeleton className="h-20 w-full" />
<Skeleton className="h-20 w-full" />
<Skeleton className="h-20 w-full" />
</div>
{:else if platformApp}
<div class="mx-auto max-w-3xl space-y-8">
<!-- Basic Information -->
<div class="space-y-6">
<div class="space-y-2">
<Label for="name">Name *</Label>
<Input
id="name"
bind:value={formData.name}
placeholder="Enter platform app name"
required
/>
</div>

<div class="space-y-2">
<Label for="webhook_url">Webhook URL *</Label>
<Input
id="webhook_url"
type="url"
bind:value={formData.webhook_url}
placeholder="https://example.com/webhook"
required
/>
</div>

<!-- Token Display -->
<div class="space-y-2">
<Label>API Token</Label>
<div class="flex gap-2">
<Input
value={platformApp.token ? maskToken(platformApp.token) : 'No token available'}
readonly
class="flex-1"
/>
<Button
variant="outline"
on:click={() => (showRegenerateDialog = true)}
class="gap-2"
>
<RefreshCw class="h-4 w-4" />
Regenerate
</Button>
</div>
<p class="text-sm text-slate-11">
Token is masked for security. Regenerate to get a new token.
</p>
</div>
</div>

<!-- Actions -->
<div class="flex items-center gap-3 border-t border-slate-6 pt-6">
<Button on:click={handleSave} disabled={saving} class="bg-iris-9 hover:bg-iris-10">
{saving ? 'Saving...' : 'Save Changes'}
</Button>
<Button variant="outline" href="/app/super_admin/platform-apps">Cancel</Button>
<Button
variant="destructive"
on:click={() => (showDeleteDialog = true)}
class="ml-auto gap-2"
>
<Trash2 class="h-4 w-4" />
Delete
</Button>
</div>
</div>
{/if}
</div>
</div>

<!-- Delete Confirmation Dialog -->
<Dialog.Root bind:open={showDeleteDialog}>
<Dialog.Content>
<Dialog.Header>
<Dialog.Title>Delete Platform App</Dialog.Title>
<Dialog.Description>
Are you sure you want to delete this platform app? This action cannot be undone.
</Dialog.Description>
</Dialog.Header>
<Dialog.Footer>
<Button variant="outline" on:click={() => (showDeleteDialog = false)}>Cancel</Button>
<Button variant="destructive" on:click={handleDelete}>Delete</Button>
</Dialog.Footer>
</Dialog.Content>
</Dialog.Root>

<!-- Regenerate Token Confirmation Dialog -->
<Dialog.Root bind:open={showRegenerateDialog}>
<Dialog.Content>
<Dialog.Header>
<Dialog.Title>Regenerate API Token</Dialog.Title>
<Dialog.Description>
This will invalidate the current token. Any applications using the old token will stop
working. Are you sure?
</Dialog.Description>
</Dialog.Header>
<Dialog.Footer>
<Button variant="outline" on:click={() => (showRegenerateDialog = false)}>Cancel</Button>
<Button on:click={handleRegenerateToken} class="bg-iris-9 hover:bg-iris-10">
Regenerate Token
</Button>
</Dialog.Footer>
</Dialog.Content>
</Dialog.Root>

<!-- New Token Display Dialog -->
<Dialog.Root bind:open={showTokenDialog}>
<Dialog.Content>
<Dialog.Header>
<Dialog.Title>New API Token Generated</Dialog.Title>
<Dialog.Description>
<span class="text-amber-11 font-medium">Important:</span> This is the only time you'll see
this token. Copy it now and store it securely.
</Dialog.Description>
</Dialog.Header>
<div class="space-y-4">
<div class="rounded-md bg-slate-2 p-4">
<code class="break-all text-sm text-slate-12">{newToken}</code>
</div>
<Button on:click={copyToken} class="w-full gap-2">
<Copy class="h-4 w-4" />
Copy Token
</Button>
</div>
<Dialog.Footer>
<Button on:click={() => (showTokenDialog = false)} variant="outline" class="w-full">
Close
</Button>
</Dialog.Footer>
</Dialog.Content>
</Dialog.Root>
