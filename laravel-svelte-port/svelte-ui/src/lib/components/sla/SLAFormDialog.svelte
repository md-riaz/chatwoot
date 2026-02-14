<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import type { CreateSLAPolicyParams, SLAPolicy } from '$lib/api/sla';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';

  interface Props {
    open: boolean;
    mode: 'create' | 'edit';
    policy?: SLAPolicy | null;
    isSubmitting?: boolean;
  }

  let {
    open = $bindable(false),
    mode = 'create',
    policy = null,
    isSubmitting = false,
  }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateSLAPolicyParams;
    close: void;
  }>();

  let name = $state('');
  let description = $state('');
  let firstResponseMinutes = $state(60);
  let nextResponseMinutes = $state(120);
  let resolutionMinutes = $state(1440);
  let onlyDuringBusinessHours = $state(false);
  let errors = $state<Record<string, string>>({});

  $effect(() => {
    if (open) {
      errors = {};
      if (mode === 'edit' && policy) {
        name = policy.name;
        description = policy.description || '';
        firstResponseMinutes = Math.max(
          1,
          Math.round(policy.firstResponseTime / 60)
        );
        nextResponseMinutes = Math.max(
          1,
          Math.round(policy.nextResponseTime / 60)
        );
        resolutionMinutes = Math.max(1, Math.round(policy.resolutionTime / 60));
        onlyDuringBusinessHours = policy.onlyDuringBusinessHours;
      } else {
        name = '';
        description = '';
        firstResponseMinutes = 60;
        nextResponseMinutes = 120;
        resolutionMinutes = 1440;
        onlyDuringBusinessHours = false;
      }
    }
  });

  function validatePositiveMinutes(
    value: number,
    field: string,
    label: string
  ) {
    if (!Number.isFinite(value) || value < 1) {
      errors[field] = `${label} must be at least 1 minute.`;
    }
  }

  function handleSubmit() {
    errors = {};
    if (!name.trim()) {
      errors.name = 'Policy name is required.';
    }

    validatePositiveMinutes(
      firstResponseMinutes,
      'firstResponseMinutes',
      'First response time'
    );
    validatePositiveMinutes(
      nextResponseMinutes,
      'nextResponseMinutes',
      'Next response time'
    );
    validatePositiveMinutes(
      resolutionMinutes,
      'resolutionMinutes',
      'Resolution time'
    );

    if (Object.keys(errors).length > 0) {
      return;
    }

    dispatch('submit', {
      name: name.trim(),
      description: description.trim() || undefined,
      firstResponseTime: Math.round(firstResponseMinutes * 60),
      nextResponseTime: Math.round(nextResponseMinutes * 60),
      resolutionTime: Math.round(resolutionMinutes * 60),
      onlyDuringBusinessHours,
    });
  }
</script>

<Dialog.Root {open} onOpenChange={value => (open = value)}>
  <Dialog.Content class="max-w-2xl">
    <Dialog.Header>
      <Dialog.Title
        >{mode === 'create'
          ? 'Create SLA Policy'
          : 'Edit SLA Policy'}</Dialog.Title
      >
      <Dialog.Description>
        Define response and resolution targets in minutes.
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-4">
      <div class="space-y-2">
        <Label for="sla-name">Policy Name</Label>
        <Input id="sla-name" bind:value={name} disabled={isSubmitting} />
        {#if errors.name}<p class="text-sm text-destructive">
            {errors.name}
          </p>{/if}
      </div>

      <div class="space-y-2">
        <Label for="sla-description">Description</Label>
        <Textarea
          id="sla-description"
          bind:value={description}
          rows={3}
          disabled={isSubmitting}
        />
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="space-y-2">
          <Label for="first-response">First response (min)</Label>
          <Input
            id="first-response"
            type="number"
            min="1"
            bind:value={firstResponseMinutes}
            disabled={isSubmitting}
          />
          {#if errors.firstResponseMinutes}<p class="text-sm text-destructive">
              {errors.firstResponseMinutes}
            </p>{/if}
        </div>
        <div class="space-y-2">
          <Label for="next-response">Next response (min)</Label>
          <Input
            id="next-response"
            type="number"
            min="1"
            bind:value={nextResponseMinutes}
            disabled={isSubmitting}
          />
          {#if errors.nextResponseMinutes}<p class="text-sm text-destructive">
              {errors.nextResponseMinutes}
            </p>{/if}
        </div>
        <div class="space-y-2">
          <Label for="resolution">Resolution (min)</Label>
          <Input
            id="resolution"
            type="number"
            min="1"
            bind:value={resolutionMinutes}
            disabled={isSubmitting}
          />
          {#if errors.resolutionMinutes}<p class="text-sm text-destructive">
              {errors.resolutionMinutes}
            </p>{/if}
        </div>
      </div>

      <div class="flex items-center justify-between rounded-md border p-3">
        <div>
          <p class="text-sm font-medium">Only during business hours</p>
          <p class="text-xs text-muted-foreground">
            Pause SLA timers outside configured business hours.
          </p>
        </div>
        <Switch
          bind:checked={onlyDuringBusinessHours}
          disabled={isSubmitting}
        />
      </div>
    </div>

    <Dialog.Footer>
      <Button
        variant="outline"
        onclick={() => dispatch('close')}
        disabled={isSubmitting}>Cancel</Button
      >
      <Button onclick={handleSubmit} disabled={isSubmitting}
        >{isSubmitting
          ? 'Saving...'
          : mode === 'create'
            ? 'Create Policy'
            : 'Update Policy'}</Button
      >
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
