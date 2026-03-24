<script lang="ts">
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import InboxSettingsHeader from './InboxSettingsHeader.svelte';
  import InboxSettingsTabs from './InboxSettingsTabs.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';

  type WorkingHourRow = {
    day_of_week: number;
    closed_all_day: boolean;
    open_hour: number;
    open_minutes: number;
    close_hour: number;
    close_minutes: number;
    open_all_day: boolean;
  };

  const dayLabels = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
  ];

  function defaultWorkingHours(): WorkingHourRow[] {
    return dayLabels.map((_, index) => ({
      day_of_week: index,
      closed_all_day: index === 0 || index === 6,
      open_hour: 9,
      open_minutes: 0,
      close_hour: 18,
      close_minutes: 0,
      open_all_day: false,
    }));
  }

  function parseWorkingHour(raw: Record<string, any>, index: number): WorkingHourRow {
    return {
      day_of_week:
        Number(raw.day_of_week ?? raw.dayOfWeek ?? index) || index,
      closed_all_day: Boolean(raw.closed_all_day ?? raw.closedAllDay ?? false),
      open_hour: Number(raw.open_hour ?? raw.openHour ?? 9),
      open_minutes: Number(raw.open_minutes ?? raw.openMinutes ?? 0),
      close_hour: Number(raw.close_hour ?? raw.closeHour ?? 18),
      close_minutes: Number(raw.close_minutes ?? raw.closeMinutes ?? 0),
      open_all_day: Boolean(raw.open_all_day ?? raw.openAllDay ?? false),
    };
  }

  function formatTime(hour: number, minutes: number): string {
    return `${String(hour).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
  }

  function applyTime(value: string, row: WorkingHourRow, key: 'open' | 'close') {
    const [hours, minutes] = value.split(':').map(Number);
    if (Number.isNaN(hours) || Number.isNaN(minutes)) return;

    if (key === 'open') {
      row.open_hour = hours;
      row.open_minutes = minutes;
      return;
    }

    row.close_hour = hours;
    row.close_minutes = minutes;
  }

  let accountId = $derived($page.params.accountId ?? '');
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(
    inboxesStore.allInboxes.find(item => item.id === inboxId) ?? null
  );

  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdating);

  let workingHoursEnabled = $state(false);
  let timezone = $state('UTC');
  let outOfOfficeMessage = $state('');
  let schedule = $state<WorkingHourRow[]>(defaultWorkingHours());
  let successMessage = $state('');
  let errorMessage = $state('');

  $effect(() => {
    if (inboxId) {
      inboxesStore.fetchInbox(inboxId);
    }
  });

  $effect(() => {
    if (!inbox) return;

    workingHoursEnabled = inbox.workingHoursEnabled ?? false;
    timezone = inbox.timezone ?? 'UTC';
    outOfOfficeMessage = inbox.outOfOfficeMessage ?? '';

    const workingHours = Array.isArray(inbox.workingHours) ? inbox.workingHours : [];
    schedule =
      workingHours.length > 0
        ? workingHours.map((item, index) =>
            parseWorkingHour(item as Record<string, any>, index)
          )
        : defaultWorkingHours();
  });

  async function handleSave() {
    successMessage = '';
    errorMessage = '';

    const inboxUpdated = await inboxesStore.updateInbox(inboxId, {
      working_hours_enabled: workingHoursEnabled,
      timezone,
      out_of_office_message: outOfOfficeMessage,
    });

    if (!inboxUpdated) {
      errorMessage = inboxesStore.error || 'Failed to update business hour settings';
      return;
    }

    const hoursUpdated = await inboxesStore.updateWorkingHours(inboxId, schedule);
    if (!hoursUpdated) {
      errorMessage = inboxesStore.error || 'Failed to update business hour schedule';
      return;
    }

    successMessage = 'Business hour settings updated successfully';
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader {accountId} {inbox} />

  {#if inbox}
    <InboxSettingsTabs
      {accountId}
      inboxId={inbox.id}
      channelType={inbox.channelType}
      active="business-hours"
    />
  {/if}

  {#if successMessage}
    <Card.Root class="border-green-200 bg-green-50">
      <Card.Content class="p-4 text-green-800">{successMessage}</Card.Content>
    </Card.Root>
  {/if}

  {#if errorMessage}
    <Card.Root class="border-red-200 bg-red-50">
      <Card.Content class="p-4 text-red-800">{errorMessage}</Card.Content>
    </Card.Root>
  {/if}

  {#if isLoading}
    <div class="py-20 text-center text-muted-foreground">Loading business hour settings...</div>
  {:else if !inbox}
    <div class="py-20 text-center text-muted-foreground">Inbox not found</div>
  {:else}
    <Card.Root>
      <Card.Header>
        <Card.Title>Business Hours</Card.Title>
        <Card.Description>
          Set the schedule and out-of-office message shown for this inbox.
        </Card.Description>
      </Card.Header>
      <Card.Content class="space-y-6">
        <div class="flex items-center justify-between rounded-lg border p-4">
          <div class="space-y-0.5">
            <Label>Enable Business Hours</Label>
            <p class="text-sm text-muted-foreground">
              Show availability and out-of-office behavior for this inbox.
            </p>
          </div>
          <Switch bind:checked={workingHoursEnabled} />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label for="timezone">Timezone</Label>
            <Input id="timezone" bind:value={timezone} placeholder="UTC" />
          </div>

          <div class="space-y-2">
            <Label for="out-of-office">Out of Office Message</Label>
            <Textarea id="out-of-office" bind:value={outOfOfficeMessage} rows={3} />
          </div>
        </div>

        <div class="space-y-3">
          {#each schedule as row, index}
            <div class="rounded-lg border p-4">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="font-medium">{dayLabels[index]}</h3>
                <Switch bind:checked={row.closed_all_day} />
              </div>

              {#if !row.closed_all_day}
                <div class="grid gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label for={`open-${index}`}>Open</Label>
                    <input
                      id={`open-${index}`}
                      type="time"
                      class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                      value={formatTime(row.open_hour, row.open_minutes)}
                      onchange={(event) =>
                        applyTime((event.currentTarget as HTMLInputElement).value, row, 'open')}
                    />
                  </div>
                  <div class="space-y-2">
                    <Label for={`close-${index}`}>Close</Label>
                    <input
                      id={`close-${index}`}
                      type="time"
                      class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                      value={formatTime(row.close_hour, row.close_minutes)}
                      onchange={(event) =>
                        applyTime((event.currentTarget as HTMLInputElement).value, row, 'close')}
                    />
                  </div>
                </div>
              {/if}
            </div>
          {/each}
        </div>
      </Card.Content>
      <Card.Footer class="justify-end">
        <Button onclick={handleSave} disabled={isUpdating}>
          {isUpdating ? 'Saving...' : 'Save Business Hours'}
        </Button>
      </Card.Footer>
    </Card.Root>
  {/if}
</div>
