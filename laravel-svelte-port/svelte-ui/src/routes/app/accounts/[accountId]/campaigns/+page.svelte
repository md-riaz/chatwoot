<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { campaignsStore } from '$lib/stores/campaigns.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { Button } from '$lib/components/ui/button';
  import { CAMPAIGN_TYPES } from '$lib/api/campaigns';
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

  let accountId = $derived($page.params.accountId);
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
    
    // Determine which dialog to show based on campaign channel type
    const channelType = campaign.inbox?.channelType;
    if (channelType === 'Channel::WebWidget') {
      showEditLiveChatDialog = true;
    } else if (channelType === 'Channel::Sms' || channelType === 'Channel::TwilioSms') {
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

  function handleViewCampaign(campaignId: number) {
    goto(`/app/accounts/${accountId}/campaigns/${campaignId}`);
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

<div class="campaigns-page p-6">
  <div class="campaigns-header mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Campaigns</h1>
      <p class="text-gray-600 mt-1">
        Manage your marketing campaigns across different channels
      </p>
    </div>
    
    <!-- Create Campaign Dropdown -->
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
  {:else if campaigns.length === 0}
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
      <!-- Live Chat Campaigns -->
      {#if liveChatCampaigns.length > 0}
        <section>
          <h2 class="text-lg font-semibold mb-4">Live Chat Campaigns</h2>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {#each liveChatCampaigns as campaign}
              <div
                class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                onclick={() => handleViewCampaign(campaign.id)}
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
                <div class="flex gap-2" onclick={(e: MouseEvent) => e.stopPropagation()}>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() => handleEditCampaign(campaign)}
                  >
                    Edit
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() => handleToggleStatus(campaign.id)}
                  >
                    {campaign.enabled ? 'Disable' : 'Enable'}
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    onclick={() => handleDeleteCampaign(campaign.id)}
                  >
                    Delete
                  </Button>
                </div>
              </div>
            {/each}
          </div>
        </section>
      {/if}

      <!-- SMS Campaigns -->
      {#if smsCampaigns.length > 0}
        <section>
          <h2 class="text-lg font-semibold mb-4">SMS Campaigns</h2>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {#each smsCampaigns as campaign}
              <div
                class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                onclick={() => handleViewCampaign(campaign.id)}
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
                <div class="flex gap-2" onclick={(e: MouseEvent) => e.stopPropagation()}>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() => handleEditCampaign(campaign)}
                  >
                    Edit
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() => handleToggleStatus(campaign.id)}
                  >
                    {campaign.enabled ? 'Disable' : 'Enable'}
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    onclick={() => handleDeleteCampaign(campaign.id)}
                  >
                    Delete
                  </Button>
                </div>
              </div>
            {/each}
          </div>
        </section>
      {/if}

      <!-- WhatsApp Campaigns -->
      {#if whatsappCampaigns.length > 0}
        <section>
          <h2 class="text-lg font-semibold mb-4">WhatsApp Campaigns</h2>
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {#each whatsappCampaigns as campaign}
              <div
                class="campaign-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                onclick={() => handleViewCampaign(campaign.id)}
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
                <div class="flex gap-2" onclick={(e: MouseEvent) => e.stopPropagation()}>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() => handleEditCampaign(campaign)}
                  >
                    Edit
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() => handleToggleStatus(campaign.id)}
                  >
                    {campaign.enabled ? 'Disable' : 'Enable'}
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    onclick={() => handleDeleteCampaign(campaign.id)}
                  >
                    Delete
                  </Button>
                </div>
              </div>
            {/each}
          </div>
        </section>
      {/if}
    </div>
  {/if}
</div>

<!-- Campaign Dialogs -->
<LiveChatCampaignDialog
  bind:open={showCreateLiveChatDialog}
  mode="create"
  on:submit={handleSubmitCreate}
/>

<LiveChatCampaignDialog
  bind:open={showEditLiveChatDialog}
  mode="edit"
  campaign={editingCampaign}
  on:submit={handleSubmitEdit}
/>

<SMSCampaignDialog
  bind:open={showCreateSMSDialog}
  mode="create"
  on:submit={handleSubmitCreate}
/>

<SMSCampaignDialog
  bind:open={showEditSMSDialog}
  mode="edit"
  campaign={editingCampaign}
  on:submit={handleSubmitEdit}
/>

<WhatsAppCampaignDialog
  bind:open={showCreateWhatsAppDialog}
  mode="create"
  on:submit={handleSubmitCreate}
/>

<WhatsAppCampaignDialog
  bind:open={showEditWhatsAppDialog}
  mode="edit"
  campaign={editingCampaign}
  on:submit={handleSubmitEdit}
/>

<style>
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
</style>
