<script lang="ts">
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { authStore } from '$lib/stores/auth.svelte';

  type RoleDraft = { name: string; permissions: string };

  let draft = $state<RoleDraft>({ name: '', permissions: '' });
  let roles = $state<{ name: string; permissions: string[] }[]>([]);

  $effect(() => {
    roles = authStore.uiSettings?.customRoles || [];
  });

  async function addRole() {
    if (!draft.name.trim() || !draft.permissions.trim()) return;

    const nextRole = {
      name: draft.name.trim(),
      permissions: draft.permissions
        .split(',')
        .map(permission => permission.trim())
        .filter(Boolean),
    };

    const nextRoles = [...roles, nextRole];
    await authStore.updateUISettings({
      ...authStore.uiSettings,
      customRoles: nextRoles,
    });

    draft = { name: '', permissions: '' };
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Custom Roles</h1>
    <p class="text-muted-foreground mt-2">
      Create account-specific roles and permission bundles.
    </p>
  </div>

  <Card.Root>
    <Card.Header>
      <Card.Title>Create Role</Card.Title>
      <Card.Description
        >Define role name and comma-separated permissions (for example:
        report_manage, inbox_manage).</Card.Description
      >
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="space-y-2">
        <Label for="role-name">Role Name</Label>
        <Input
          id="role-name"
          bind:value={draft.name}
          placeholder="QA Manager"
        />
      </div>
      <div class="space-y-2">
        <Label for="role-permissions">Permissions</Label>
        <Input
          id="role-permissions"
          bind:value={draft.permissions}
          placeholder="report_manage, conversation_manage"
        />
      </div>
      <div class="flex justify-end">
        <Button onclick={addRole}>Add Role</Button>
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root>
    <Card.Header>
      <Card.Title>Configured Roles</Card.Title>
    </Card.Header>
    <Card.Content>
      {#if roles.length === 0}
        <p class="text-sm text-muted-foreground">
          No custom roles configured yet.
        </p>
      {:else}
        <div class="space-y-3">
          {#each roles as role}
            <div class="rounded-md border p-3">
              <p class="font-semibold">{role.name}</p>
              <p class="text-sm text-muted-foreground">
                {role.permissions.join(', ')}
              </p>
            </div>
          {/each}
        </div>
      {/if}
    </Card.Content>
  </Card.Root>
</div>
