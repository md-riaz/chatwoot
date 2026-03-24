<script lang="ts">
  import { goto } from '$app/navigation';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    accountId: string | number;
    inboxId: number;
    channelType?: string | null;
    active:
      | 'configuration'
      | 'collaborators'
      | 'imap'
      | 'smtp'
      | 'business-hours'
      | 'csat'
      | 'pre-chat-form'
      | 'widget-builder';
  }

  let { accountId, inboxId, channelType = null, active }: Props = $props();

  const tabs = $derived([
    {
      key: 'configuration',
      label: 'Configuration',
      href: () =>
        `/app/accounts/${accountId}/settings/inboxes/${inboxId}/configuration`,
    },
    {
      key: 'collaborators',
      label: 'Collaborators',
      href: () =>
        `/app/accounts/${accountId}/settings/inboxes/${inboxId}/collaborators`,
    },
    ...(channelType !== 'Channel::Voice'
      ? [
          {
            key: 'business-hours',
            label: 'Business Hours',
            href: () =>
              `/app/accounts/${accountId}/settings/inboxes/${inboxId}/business-hours`,
          },
          {
            key: 'csat',
            label: 'CSAT',
            href: () =>
              `/app/accounts/${accountId}/settings/inboxes/${inboxId}/csat`,
          },
        ]
      : []),
    ...(channelType === 'Channel::WebWidget'
      ? [
          {
            key: 'pre-chat-form',
            label: 'Pre Chat Form',
            href: () =>
              `/app/accounts/${accountId}/settings/inboxes/${inboxId}/pre-chat-form`,
          },
          {
            key: 'widget-builder',
            label: 'Widget Builder',
            href: () =>
              `/app/accounts/${accountId}/settings/inboxes/${inboxId}/widget-builder`,
          },
        ]
      : []),
    ...(channelType === 'Channel::Email'
      ? [
          {
            key: 'imap',
            label: 'IMAP',
            href: () =>
              `/app/accounts/${accountId}/settings/inboxes/${inboxId}/imap`,
          },
          {
            key: 'smtp',
            label: 'SMTP',
            href: () =>
              `/app/accounts/${accountId}/settings/inboxes/${inboxId}/smtp`,
          },
        ]
      : []),
  ] as const);
</script>

<div class="flex flex-wrap gap-2 border-b pb-4">
  {#each tabs as tab}
    <Button
      variant={active === tab.key ? 'default' : 'outline'}
      onclick={() => goto(tab.href())}
    >
      {tab.label}
    </Button>
  {/each}
</div>
