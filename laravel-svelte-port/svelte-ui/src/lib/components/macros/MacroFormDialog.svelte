<script lang="ts">
  import type { CreateMacroParams, Macro } from '$lib/api/macros';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';

  interface Props {
    open: boolean;
    mode: 'create' | 'edit';
    macro?: Macro | null;
    isSubmitting?: boolean;
    onSubmit?: (payload: CreateMacroParams) => void;
    onClose?: () => void;
  }

  let {
    open = $bindable(false),
    mode = 'create',
    macro = null,
    isSubmitting = false,
    onSubmit,
    onClose,
  }: Props = $props();

  let name = $state('');
  let visibility = $state<'global' | 'personal' | 'team'>('personal');
  let actionsJson = $state(
    '[\n  {\n    "actionName": "send_reply",\n    "actionParams": ["Thank you for reaching out."]\n  }\n]'
  );
  let errors = $state<Record<string, string>>({});
  let wasOpen = $state(false);

  $effect(() => {
    if (open && !wasOpen) {
      errors = {};
      if (mode === 'edit' && macro) {
        name = macro.name;
        visibility = macro.visibility;
        actionsJson = JSON.stringify(macro.actions, null, 2);
      } else {
        name = '';
        visibility = 'personal';
        actionsJson =
          '[\n  {\n    "actionName": "send_reply",\n    "actionParams": ["Thank you for reaching out."]\n  }\n]';
      }
    }

    wasOpen = open;
  });

  function validateAndBuildPayload(): CreateMacroParams | null {
    errors = {};

    if (!name.trim()) {
      errors.name = 'Macro name is required.';
    }

    let parsedActions: CreateMacroParams['actions'] = [];
    try {
      const parsed = JSON.parse(actionsJson);
      if (!Array.isArray(parsed) || parsed.length === 0) {
        errors.actions = 'Actions must be a non-empty JSON array.';
      } else if (
        !parsed.every(
          action =>
            typeof action?.actionName === 'string' &&
            action.actionName.trim().length > 0
        )
      ) {
        errors.actions = 'Each action must include a valid actionName.';
      } else {
        parsedActions = parsed;
      }
    } catch {
      errors.actions = 'Actions must be valid JSON.';
    }

    if (Object.keys(errors).length > 0) {
      return null;
    }

    return {
      name: name.trim(),
      visibility,
      actions: parsedActions,
    };
  }

  function handleSubmit() {
    const payload = validateAndBuildPayload();
    if (!payload) return;
    onSubmit?.(payload);
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
  <Dialog.Content class="max-w-2xl">
    <Dialog.Header>
      <Dialog.Title
        >{mode === 'create' ? 'Create Macro' : 'Edit Macro'}</Dialog.Title
      >
      <Dialog.Description>
        Define reusable actions that agents can apply quickly.
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-4">
      <div class="space-y-2">
        <Label for="macro-name">Name</Label>
        <Input
          id="macro-name"
          bind:value={name}
          placeholder="e.g. Welcome follow-up"
          disabled={isSubmitting}
        />
        {#if errors.name}
          <p class="text-sm text-destructive">{errors.name}</p>
        {/if}
      </div>

      <div class="space-y-2">
        <Label for="macro-visibility">Visibility</Label>
        <select
          id="macro-visibility"
          bind:value={visibility}
          class="w-full rounded-md border bg-background px-3 py-2 text-sm"
          disabled={isSubmitting}
        >
          <option value="personal">Personal</option>
          <option value="team">Team</option>
          <option value="global">Global</option>
        </select>
      </div>

      <div class="space-y-2">
        <Label for="macro-actions">Actions JSON</Label>
        <Textarea
          id="macro-actions"
          bind:value={actionsJson}
          rows={10}
          disabled={isSubmitting}
        />
        <p class="text-xs text-muted-foreground">
          Provide an array of actions with actionName and optional actionParams.
        </p>
        {#if errors.actions}
          <p class="text-sm text-destructive">{errors.actions}</p>
        {/if}
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
            ? 'Create Macro'
            : 'Update Macro'}</Button
      >
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
