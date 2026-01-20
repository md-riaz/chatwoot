<script lang="ts">
  import { Check } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import * as Popover from '$lib/components/ui/popover';
  import { _ } from '$lib/i18n';

  type SortOrder = 'newest' | 'oldest';

  interface Props {
    sortOrder?: SortOrder;
    showSnoozed?: boolean;
    showRead?: boolean;
    onChange?: (change: { sortOrder: SortOrder; showSnoozed: boolean; showRead: boolean }) => void;
  }

  let {
    sortOrder = 'newest',
    showSnoozed = true,
    showRead = true,
    onChange = undefined
  }: Props = $props();

  function emitChange(next: Partial<{ sortOrder: SortOrder; showSnoozed: boolean; showRead: boolean }>) {
    const payload = {
      sortOrder,
      showSnoozed,
      showRead,
      ...next
    };

    sortOrder = payload.sortOrder;
    showSnoozed = payload.showSnoozed;
    showRead = payload.showRead;

    if (onChange) {
      onChange(payload);
    }
  }
</script>

<Popover.Root>
  <Popover.Trigger>
    {#snippet child({ props })}
      <Button
        {...props}
        variant="ghost"
        size="sm"
        class="h-8 px-2 text-xs font-medium border rounded-md bg-background hover:bg-accent"
      >
        <span>Display</span>
        <svg
          class="ml-1 h-3 w-3 opacity-60"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path
            fill-rule="evenodd"
            d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
            clip-rule="evenodd"
          />
        </svg>
      </Button>
    {/snippet}
  </Popover.Trigger>
  <Popover.Content class="w-56 p-3 text-xs space-y-3">
    <div class="space-y-1">
      <p class="font-semibold">{$_('inbox.list.sort')}</p>
      <div class="flex gap-1">
        <Button
          variant={sortOrder === 'newest' ? 'default' : 'outline'}
          size="sm"
          class="h-7 px-2 flex-1"
          onclick={() => emitChange({ sortOrder: 'newest' })}
        >
          {$_('inbox.list.newest')}
        </Button>
        <Button
          variant={sortOrder === 'oldest' ? 'default' : 'outline'}
          size="sm"
          class="h-7 px-2 flex-1"
          onclick={() => emitChange({ sortOrder: 'oldest' })}
        >
          {$_('inbox.list.oldest')}
        </Button>
      </div>
    </div>

    <div class="space-y-1">
      <p class="font-semibold">{$_('inbox.list.display')}</p>
      <div class="space-y-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <Checkbox
            checked={showSnoozed}
            onCheckedChange={(value) =>
              emitChange({ showSnoozed: value === true })
            }
          />
          <span>{$_('inbox.list.snoozed')}</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <Checkbox
            checked={showRead}
            onCheckedChange={(value) =>
              emitChange({ showRead: value === true })
            }
          />
          <span>{$_('inbox.list.read')}</span>
        </label>
      </div>
    </div>
  </Popover.Content>
</Popover.Root>

