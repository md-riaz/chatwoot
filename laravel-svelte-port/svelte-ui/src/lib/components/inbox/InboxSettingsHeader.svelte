<script lang="ts">
  import { goto } from '$app/navigation';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import {
    Globe,
    Mail,
    Phone,
    MessageCircle,
    MessageSquare,
    Send,
    Hash,
    Instagram,
    Video,
    Plug,
    Inbox,
    ArrowLeft,
  } from 'lucide-svelte';
  import type { Inbox as InboxRecord } from '$lib/api/inboxes';

  interface Props {
    accountId: string | number;
    inbox: InboxRecord | null;
    isDeleting?: boolean;
    onDelete?: () => void;
  }

  let { accountId, inbox, isDeleting = false, onDelete }: Props = $props();

  const channelIconMap: Record<string, typeof Globe> = {
    'Channel::WebWidget': Globe,
    'Channel::Api': Plug,
    'Channel::Email': Mail,
    'Channel::Whatsapp': Phone,
    'Channel::Sms': MessageSquare,
    'Channel::TwilioSms': MessageSquare,
    'Channel::FacebookPage': MessageCircle,
    'Channel::TwitterProfile': Hash,
    'Channel::Line': MessageCircle,
    'Channel::Telegram': Send,
    'Channel::Instagram': Instagram,
    'Channel::Tiktok': Video,
    'Channel::Voice': Phone,
  };

  function getChannelTypeName(channelType: string): string {
    return channelType.replace('Channel::', '');
  }

  function getChannelIcon(channelType: string) {
    return channelIconMap[channelType] || Inbox;
  }
</script>

<div class="flex items-center justify-between">
  <div class="flex items-center gap-4">
    <Button
      variant="ghost"
      onclick={() => goto(`/app/accounts/${accountId}/settings/inboxes`)}
    >
      <ArrowLeft class="mr-1 h-4 w-4" /> Back to Inboxes
    </Button>
    {#if inbox}
      <div class="flex items-center gap-3">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted"
        >
          {#each [getChannelIcon(inbox.channelType)] as IconComponent}
            <IconComponent class="h-5 w-5 text-muted-foreground" />
          {/each}
        </div>
        <div>
          <h1 class="text-xl font-medium tracking-tight text-foreground">
            {inbox.name}
          </h1>
          <Badge variant="secondary" class="mt-1">
            {getChannelTypeName(inbox.channelType)}
          </Badge>
        </div>
      </div>
    {/if}
  </div>
  {#if inbox && onDelete}
    <Button
      variant="destructive"
      onclick={onDelete}
      disabled={isDeleting}
    >
      {isDeleting ? 'Deleting...' : 'Delete Inbox'}
    </Button>
  {/if}
</div>
