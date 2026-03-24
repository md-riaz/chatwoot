<script lang="ts">
  import { onMount } from 'svelte';
  import { campaignsStore } from '$lib/stores/campaigns.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { Button } from '$lib/components/ui/button';
  import type { Campaign } from '$lib/api/campaigns';
  import LiveChatCampaignDialog from '$lib/components/campaigns/LiveChatCampaignDialog.svelte';
  import SMSCampaignDialog from '$lib/components/campaigns/SMSCampaignDialog.svelte';
  import WhatsAppCampaignDialog from '$lib/components/campaigns/WhatsAppCampaignDialog.svelte';
  import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
  } from '$lib/components/ui/dropdown-menu';
  import { ChevronDown } from 'lucide-svelte';

  type CampaignView = 'all' | 'livechat' | 'sms' | 'whatsapp';

  interface Props {
    view?: CampaignView;
  }

  let { view = 'all' }: Props = $props();

  let isLoading = $derived(campaignsStore.isLoading);
  let campaigns = $derived(campaignsStore.sortedCampaigns);
  let liveChatCampaigns = $derived(campaignsStore.liveChatCampaigns);
  let smsCampaigns = $derived(campaignsStore.smsCampaigns);
  let whatsappCampaigns = $derived(campaignsStore.whatsappCampaigns);

  let showCreateLiveChatDialog = $state(false);
  let showCreateSMSDialog = $state(false);
  let showCreateWhatsAppDialog = $state(false);
  let showEditLiveChatDialog = $state(false);
  let showEditSMSDialog = $state(false);
  let showEditWhatsAppDialog = $state(false);
  let editingCampaign = $state<Campaign | null>(null);

  const viewTitle = $derived(
    view === 'livechat'
      ? 'Live Chat Campaigns'
      : view === 'sms'
        ? 'SMS Campaigns'
        : view === 'whatsapp'
          ? 'WhatsApp Campaigns'
          : 'Campaigns'
  );

  const viewDescription = $derived(
    view === 'livechat'
      ? 'Manage your ongoing live chat campaigns'
      : view === 'sms'
        ? 'Manage your one-off SMS campaigns'
        : view === 'whatsapp'
          ? 'Manage your one-off WhatsApp campaigns'
          : 'Manage your marketing campaigns across different channels'
  );

  const visibleCampaigns = $derived(
    view === 'livechat'
      ? liveChatCampaigns
      : view === 'sms'
        ? smsCampaigns
        : view === 'whatsapp'
          ? whatsappCampaigns
          : campaigns
  );

  onMount(() => {
    campaignsStore.fetchCampaigns();
    inboxesStore.fetchInboxes();
    labelsStore.fetchLabels();
  });

  function handleCreateLiveChatCampaign() {
    showCreateLiveChatDialog = true;
  }

  function handleCreateSMSCampaign() {
    showCreateSMSDialog = true;
  }

  function handleCreateWhatsAppCampaign() {
    showCreateWhatsAppDialog = true;
  }

  async function handleSubmitCreate(event: CustomEvent) {
    const data = event.detail;
    await campaignsStore.createCampaign(data);
    campaignsStore.fetchCampaigns();
  }

  function handleEditCampaign(campaign: Campaign) {
    editingCampaign = campaign;

    const channelType = campaign.inbox?.channelType;
    if (channelType === 'Channel::WebWidget') {
      showEditLiveChatDialog = true;
    } else if (
      channelType === 'Channel::Sms' ||
      channelType === 'Channel::TwilioSms'
    ) {
      showEditSMSDialog = true;
    } else if (channelType === 'Channel::Whatsapp') {
      showEditWhatsAppDialog = true;
    }
  }

  async function handleSubmitEdit(event: CustomEvent) {
    if (!editingCampaign) return;
    const data = event.detail;
    await campaignsStore.updateCampaign(editingCampaign.id, data);
    campaignsStore.fetchCampaigns();
    editingCampaign = null;
  }

  async function handleDeleteCampaign(campaignId: number) {
    if (confirm('Are you sure you want to delete this campaign?')) {
      await campaignsStore.deleteCampaign(campaignId);
    }
  }

  async function handleToggleStatus(campaignId: number) {
    await campaignsStore.toggleCampaignStatus(campaignId);
  }

  function getCampaignStatusClass(campaign: Campaign) {
    if (!campaign.enabled) return 'bg-gray-100 text-gray-800';
    if (campaign.campaignStatus === 'active')
      return 'bg-green-100 text-green-800';
    if (campaign.campaignStatus === 'paused')
      return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString();
  }
</script>

<div class="campaigns-page p-6 max-w-[60rem] mx-auto">
  <div class="campaigns-header mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">{viewTitle}</h1>
      <p class="text-gray-600 mt-1">
        {viewDescription}
      </p>
    </div>

    <DropdownMenu>
      <DropdownMenuTrigger>
        {#snippet child({ props })}
          <Button {...props}>
            Create Campaign
            <ChevronDown class="ml-2 h-4 w-4" />
          </Button>
        {/snippet}
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem onclick={handleCreateLiveChatCampaign}>
          Live Chat Campaign
        </DropdownMenuItem>
        <DropdownMenuItem onclick={handleCreateSMSCampaign}>
          SMS Campaign
        </DropdownMenuItem>
        <DropdownMenuItem onclick={handleCreateWhatsAppCampaign}>
          WhatsApp Campaign
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if visibleCampaigns.length === 0}
    <div class="empty-state text-center py-20">
      <div class="mb-4">
        <svg
          class="mx-auto h-16 w-16 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
          />
        </svg>
      </div>
      <h2 class="text-xl font-semibold mb-2">No campaigns yet</h2>
      <p class="text-gray-600 mb-4">
        Create your first campaign to start engaging with your audience
      </p>
      <DropdownMenu>
        <DropdownMenuTrigger>
          {#snippet child({ props })}
            <Button {...props}>
              Create Your First Campaign
              <ChevronDown class="ml-2 h-4 w-4" />
            </Button>
          {/snippet}
        </DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem onclick={handleCreateLiveChatCampaign}>
            Live Chat Campaign
          </DropdownMenuItem>
          <DropdownMenuItem onclick={handleCreateSMSCampaign}>
            SMS Campaign
          </DropdownMenuItem>
          <DropdownMenuItem onclick={handleCreateWhatsAppCampaign}>
            WhatsApp Campaign
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  {:else}
    <div class="campaigns-sections space-y-8">
      {#if view === 'all'}
        {#if liveChatCampaigns.length > 0}
          <section>
            <h2 class="text-lg font-semibold mb-4">Live Chat Campaigns</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {#each liveChatCampaigns as campaign}
                <button
                  type="button"
                  class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer text-left"
                  onclick={() => handleEditCampaign(campaign)}
                >
                  <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-lg">{campaign.title}</h3>
                    <span
                      class="px-2 py-1 rounded text-xs font-medium {getCampaignStatusClass(
                        campaign
                      )}"
                    >
                      {campaign.enabled ? campaign.campaignStatus : 'disabled'}
                    </span>
                  </div>
                  {#if campaign.description}
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                      {campaign.description}
                    </p>
                  {/if}
                  <div class="text-xs text-gray-500 mb-3">
                    <div>Inbox: {campaign.inbox?.name || 'N/A'}</div>
                    <div>Created: {formatDate(campaign.createdAt)}</div>
                  </div>
                  <div class="flex gap-2 pt-2 border-t">
                    <Button
                      size="sm"
                      variant="outline"
                      onclick={e => {
                        e.stopPropagation();
                        handleToggleStatus(campaign.id);
                      }}
                    >
                      {campaign.enabled ? 'Pause' : 'Activate'}
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onclick={e => {
                        e.stopPropagation();
                        handleDeleteCampaign(campaign.id);
                      }}
                    >
                      Delete
                    </Button>
                  </div>
                </button>
              {/each}
            </div>
          </section>
        {/if}

        {#if smsCampaigns.length > 0}
          <section>
            <h2 class="text-lg font-semibold mb-4">SMS Campaigns</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {#each smsCampaigns as campaign}
                <button
                  type="button"
                  class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer text-left"
                  onclick={() => handleEditCampaign(campaign)}
                >
                  <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-lg">{campaign.title}</h3>
                    <span
                      class="px-2 py-1 rounded text-xs font-medium {getCampaignStatusClass(
                        campaign
                      )}"
                    >
                      {campaign.enabled ? campaign.campaignStatus : 'disabled'}
                    </span>
                  </div>
                  {#if campaign.message}
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                      {campaign.message}
                    </p>
                  {/if}
                  <div class="text-xs text-gray-500 mb-3">
                    <div>Inbox: {campaign.inbox?.name || 'N/A'}</div>
                    <div>Created: {formatDate(campaign.createdAt)}</div>
                  </div>
                  <div class="flex gap-2 pt-2 border-t">
                    <Button
                      size="sm"
                      variant="outline"
                      onclick={e => {
                        e.stopPropagation();
                        handleToggleStatus(campaign.id);
                      }}
                    >
                      {campaign.enabled ? 'Pause' : 'Activate'}
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onclick={e => {
                        e.stopPropagation();
                        handleDeleteCampaign(campaign.id);
                      }}
                    >
                      Delete
                    </Button>
                  </div>
                </button>
              {/each}
            </div>
          </section>
        {/if}

        {#if whatsappCampaigns.length > 0}
          <section>
            <h2 class="text-lg font-semibold mb-4">WhatsApp Campaigns</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {#each whatsappCampaigns as campaign}
                <button
                  type="button"
                  class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer text-left"
                  onclick={() => handleEditCampaign(campaign)}
                >
                  <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-lg">{campaign.title}</h3>
                    <span
                      class="px-2 py-1 rounded text-xs font-medium {getCampaignStatusClass(
                        campaign
                      )}"
                    >
                      {campaign.enabled ? campaign.campaignStatus : 'disabled'}
                    </span>
                  </div>
                  {#if campaign.message}
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                      {campaign.message}
                    </p>
                  {/if}
                  <div class="text-xs text-gray-500 mb-3">
                    <div>Inbox: {campaign.inbox?.name || 'N/A'}</div>
                    <div>Created: {formatDate(campaign.createdAt)}</div>
                  </div>
                  <div class="flex gap-2 pt-2 border-t">
                    <Button
                      size="sm"
                      variant="outline"
                      onclick={e => {
                        e.stopPropagation();
                        handleToggleStatus(campaign.id);
                      }}
                    >
                      {campaign.enabled ? 'Pause' : 'Activate'}
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onclick={e => {
                        e.stopPropagation();
                        handleDeleteCampaign(campaign.id);
                      }}
                    >
                      Delete
                    </Button>
                  </div>
                </button>
              {/each}
            </div>
          </section>
        {/if}
      {:else}
        <section>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {#each visibleCampaigns as campaign}
              <button
                type="button"
                class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer text-left"
                onclick={() => handleEditCampaign(campaign)}
              >
                <div class="flex justify-between items-start mb-2">
                  <h3 class="font-semibold text-lg">{campaign.title}</h3>
                  <span
                    class="px-2 py-1 rounded text-xs font-medium {getCampaignStatusClass(
                      campaign
                    )}"
                  >
                    {campaign.enabled ? campaign.campaignStatus : 'disabled'}
                  </span>
                </div>
                {#if campaign.description || campaign.message}
                  <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                    {campaign.description || campaign.message}
                  </p>
                {/if}
                <div class="text-xs text-gray-500 mb-3">
                  <div>Inbox: {campaign.inbox?.name || 'N/A'}</div>
                  <div>Created: {formatDate(campaign.createdAt)}</div>
                </div>
                <div class="flex gap-2 pt-2 border-t">
                  <Button
                    size="sm"
                    variant="outline"
                    onclick={e => {
                      e.stopPropagation();
                      handleToggleStatus(campaign.id);
                    }}
                  >
                    {campaign.enabled ? 'Pause' : 'Activate'}
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    onclick={e => {
                      e.stopPropagation();
                      handleDeleteCampaign(campaign.id);
                    }}
                  >
                    Delete
                  </Button>
                </div>
              </button>
            {/each}
          </div>
        </section>
      {/if}
    </div>
  {/if}
</div>

<LiveChatCampaignDialog
  bind:open={showCreateLiveChatDialog}
  on:close={() => (showCreateLiveChatDialog = false)}
  on:submit={handleSubmitCreate}
/>

<SMSCampaignDialog
  bind:open={showCreateSMSDialog}
  on:close={() => (showCreateSMSDialog = false)}
  on:submit={handleSubmitCreate}
/>

<WhatsAppCampaignDialog
  bind:open={showCreateWhatsAppDialog}
  on:close={() => (showCreateWhatsAppDialog = false)}
  on:submit={handleSubmitCreate}
/>

{#if editingCampaign}
  {#if showEditLiveChatDialog}
    <LiveChatCampaignDialog
      bind:open={showEditLiveChatDialog}
      mode="edit"
      campaign={editingCampaign}
      on:close={() => {
        showEditLiveChatDialog = false;
        editingCampaign = null;
      }}
      on:submit={handleSubmitEdit}
    />
  {/if}

  {#if showEditSMSDialog}
    <SMSCampaignDialog
      bind:open={showEditSMSDialog}
      mode="edit"
      campaign={editingCampaign}
      on:close={() => {
        showEditSMSDialog = false;
        editingCampaign = null;
      }}
      on:submit={handleSubmitEdit}
    />
  {/if}

  {#if showEditWhatsAppDialog}
    <WhatsAppCampaignDialog
      bind:open={showEditWhatsAppDialog}
      mode="edit"
      campaign={editingCampaign}
      on:close={() => {
        showEditWhatsAppDialog = false;
        editingCampaign = null;
      }}
      on:submit={handleSubmitEdit}
    />
  {/if}
{/if}
