<script lang="ts">
  import type { Automation, CreateAutomationParams } from '$lib/api/automation';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';

  interface Props {
    open: boolean;
    mode: 'create' | 'edit';
    automation?: Automation | null;
    isSubmitting?: boolean;
    onSubmit?: (payload: CreateAutomationParams) => void;
    onClose?: () => void;
  }

  let {
    open = $bindable(false),
    mode = 'create',
    automation = null,
    isSubmitting = false,
    onSubmit,
    onClose,
  }: Props = $props();

  let name = $state('');
  let description = $state('');
  let eventName = $state('conversation_created');
  let active = $state(true);
  const defaultConditionsJson =
    '[\n  {\n    "attributeKey": "status",\n    "filterOperator": "equal_to",\n    "values": ["open"]\n  }\n]';
  const defaultActionsJson =
    '[\n  {\n    "actionName": "send_email_transcript"\n  }\n]';
  let conditionsJson = $state(defaultConditionsJson);
  let actionsJson = $state(defaultActionsJson);
  let errors = $state<Record<string, string>>({});
  let wasOpen = $state(false);

  $effect(() => {
    if (open && !wasOpen) {
      errors = {};
      if (mode === 'edit' && automation) {
        name = automation.name;
        description = automation.description || '';
        eventName = automation.eventName;
        active = automation.active;
        conditionsJson = JSON.stringify(automation.conditions, null, 2);
        actionsJson = JSON.stringify(automation.actions, null, 2);
      } else {
        name = '';
        description = '';
        eventName = 'conversation_created';
        active = true;
        conditionsJson = defaultConditionsJson;
        actionsJson = defaultActionsJson;
      }
    }

    wasOpen = open;
  });

  function parseJsonArray<T>(value: string, field: string): T[] | null {
    try {
      const parsed = JSON.parse(value);
      if (!Array.isArray(parsed) || parsed.length === 0) {
        errors[field] =
          `${field === 'conditions' ? 'Conditions' : 'Actions'} must be a non-empty JSON array.`;
        return null;
      }
      return parsed;
    } catch {
      errors[field] =
        `${field === 'conditions' ? 'Conditions' : 'Actions'} must be valid JSON.`;
      return null;
    }
  }

  function handleSubmit() {
    errors = {};

    if (!name.trim()) {
      errors.name = 'Automation name is required.';
    }

    if (!eventName.trim()) {
      errors.eventName = 'Event name is required.';
    }

    const conditions = parseJsonArray<
      CreateAutomationParams['conditions'][number]
    >(conditionsJson, 'conditions');
    const actions = parseJsonArray<CreateAutomationParams['actions'][number]>(
      actionsJson,
      'actions'
    );

    if (!conditions || !actions || Object.keys(errors).length > 0) {
      return;
    }

    onSubmit?.({
      name: name.trim(),
      description: description.trim() || undefined,
      eventName: eventName.trim(),
      conditions,
      actions,
      active,
    });
  }
</script>

<Dialog.Root
  {open}
  onOpenChange={value => {
    open = value;
    if (!value) {
      onClose?.();
    }
  }}
>
  <Dialog.Content class="max-w-3xl max-h-[85vh] overflow-y-auto">
    <Dialog.Header>
      <Dialog.Title
        >{mode === 'create'
          ? 'Create Automation Rule'
          : 'Edit Automation Rule'}</Dialog.Title
      >
      <Dialog.Description>
        Configure events, conditions, and actions for this automation.
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-4">
      <div class="space-y-2">
        <Label for="automation-name">Name</Label>
        <Input id="automation-name" bind:value={name} disabled={isSubmitting} />
        {#if errors.name}<p class="text-sm text-destructive">
            {errors.name}
          </p>{/if}
      </div>

      <div class="space-y-2">
        <Label for="automation-description">Description</Label>
        <Textarea
          id="automation-description"
          bind:value={description}
          rows={3}
          disabled={isSubmitting}
        />
      </div>

      <div class="space-y-2">
        <Label for="automation-event">Event Name</Label>
        <Input
          id="automation-event"
          bind:value={eventName}
          placeholder="conversation_created"
          disabled={isSubmitting}
        />
        {#if errors.eventName}<p class="text-sm text-destructive">
            {errors.eventName}
          </p>{/if}
      </div>

      <div class="flex items-center justify-between rounded-md border p-3">
        <div>
          <p class="text-sm font-medium">Active</p>
          <p class="text-xs text-muted-foreground">
            Inactive automations will not run.
          </p>
        </div>
        <Switch bind:checked={active} disabled={isSubmitting} />
      </div>

      <div class="space-y-2">
        <Label for="automation-conditions">Conditions JSON</Label>
        <Textarea
          id="automation-conditions"
          bind:value={conditionsJson}
          rows={8}
          disabled={isSubmitting}
        />
        {#if errors.conditions}<p class="text-sm text-destructive">
            {errors.conditions}
          </p>{/if}
      </div>

      <div class="space-y-2">
        <Label for="automation-actions">Actions JSON</Label>
        <Textarea
          id="automation-actions"
          bind:value={actionsJson}
          rows={8}
          disabled={isSubmitting}
        />
        {#if errors.actions}<p class="text-sm text-destructive">
            {errors.actions}
          </p>{/if}
      </div>
    </div>

    <Dialog.Footer>
      <Button
        variant="outline"
        onclick={() => onClose?.()}
        disabled={isSubmitting}>Cancel</Button
      >
      <Button onclick={handleSubmit} disabled={isSubmitting}
        >{isSubmitting
          ? 'Saving...'
          : mode === 'create'
            ? 'Create Automation'
            : 'Update Automation'}</Button
      >
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
