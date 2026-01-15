<script lang="ts">
  /**
   * AgentForm
   * Form for creating/editing agents
   */
  import { onMount, createEventDispatcher } from 'svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import * as Select from '$lib/components/ui/select';
  import type { Agent, CreateAgentParams } from '$lib/api/agents';

  interface Props {
    mode: 'create' | 'edit';
    agent?: Agent | null;
  }

  let { mode, agent = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateAgentParams;
    cancel: void;
  }>();

  let name = $state('');
  let email = $state('');
  let role = $state<'administrator' | 'agent'>('agent');

  let errors = $state<Record<string, string>>({});
  let isSubmitting = $state(false);

  const roleOptions = [
    { value: 'administrator', label: 'Administrator' },
    { value: 'agent', label: 'Agent' },
  ];

  onMount(() => {
    // If editing, populate form with agent data
    if (mode === 'edit' && agent) {
      name = agent.name;
      email = agent.email;
      role = agent.role;
    }
  });

  function validateEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function validateForm(): boolean {
    errors = {};
    let isValid = true;

    if (!name || name.trim().length < 1) {
      errors.name = 'Name is required';
      isValid = false;
    }

    if (!email || email.trim().length < 1) {
      errors.email = 'Email is required';
      isValid = false;
    } else if (!validateEmail(email)) {
      errors.email = 'Please enter a valid email address';
      isValid = false;
    }

    if (!role) {
      errors.role = 'Role is required';
      isValid = false;
    }

    return isValid;
  }

  function handleSubmit() {
    if (!validateForm()) {
      return;
    }

    isSubmitting = true;

    const agentData: CreateAgentParams = {
      name: name.trim(),
      email: email.trim(),
      role,
    };

    dispatch('submit', agentData);
    isSubmitting = false;
  }

  function handleCancel() {
    dispatch('cancel');
  }
</script>

<div class="space-y-6">
  <div class="space-y-4">
    <div class="space-y-2">
      <Label for="name">Full Name *</Label>
      <Input
        id="name"
        bind:value={name}
        placeholder="e.g., John Doe"
        class={errors.name ? 'border-red-500' : ''}
      />
      {#if errors.name}
        <p class="text-sm text-red-500">{errors.name}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="email">Email Address *</Label>
      <Input
        id="email"
        type="email"
        bind:value={email}
        placeholder="john@example.com"
        disabled={mode === 'edit'}
        class={errors.email ? 'border-red-500' : ''}
      />
      {#if mode === 'edit'}
        <p class="text-sm text-muted-foreground">
          Email cannot be changed after creation
        </p>
      {:else}
        <p class="text-sm text-muted-foreground">
          An invitation will be sent to this email address
        </p>
      {/if}
      {#if errors.email}
        <p class="text-sm text-red-500">{errors.email}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="role">Role *</Label>
      <Select.Root
        value={role ? [role] : []}
        onValueChange={(value: any) => {
          const v = Array.isArray(value) ? value[0] : undefined;
          if (v) role = v as 'administrator' | 'agent';
        }}
      >
        <Select.Trigger class={errors.role ? 'border-red-500' : ''}>
          <Select.Value placeholder="Select a role" />
        </Select.Trigger>
        <Select.Content>
          {#each roleOptions as roleOption}
            <Select.Item value={roleOption.value} label={roleOption.label}>
              {roleOption.label}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
      <p class="text-sm text-muted-foreground">
        {role === 'administrator'
          ? 'Administrators have full access to all features and settings'
          : 'Agents can manage conversations and contacts'}
      </p>
      {#if errors.role}
        <p class="text-sm text-red-500">{errors.role}</p>
      {/if}
    </div>
  </div>

  <div class="flex justify-end gap-2">
    <Button variant="outline" onclick={handleCancel} disabled={isSubmitting}>
      Cancel
    </Button>
    <Button onclick={handleSubmit} disabled={isSubmitting}>
      {isSubmitting ? 'Saving...' : mode === 'create' ? 'Send Invitation' : 'Update Agent'}
    </Button>
  </div>
</div>
