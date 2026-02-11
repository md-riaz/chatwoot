
> @chatwoot/svelte-ui@1.0.0 check /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
> svelte-kit sync && svelte-check --tsconfig ./tsconfig.json

Loading svelte-check in workspace: /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
Getting Svelte diagnostics...

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/reports.svelte.ts:539:9
Error: Duplicate function implementation. 
   */
  async dispatchAction(actionKey: string, params?: any): Promise<void> {
    // Map action keys to methods

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/reports.svelte.ts:793:9
Error: Duplicate function implementation. 
   */
  async dispatchAction(actionName: string, params: any): Promise<void> {
    const actions: Record<string, (params: any) => Promise<void>> = {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:226:6
Error: This condition will always return true since this function is always defined. Did you mean to call it instead? (ts)

{#if filterItemsList}
  <ReportFilters

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:229:6
Error: Type '() => Agent[] | Inbox[] | Label[] | Team[]' is not assignable to type 'any[]'. (ts)
    {type}
    {filterItemsList}
    {groupByfilterItemsList}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:69:5
Warn: Unknown at rule @apply (css)
  .presence-indicator {
    @apply inline-flex items-center;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:73:5
Warn: Unknown at rule @apply (css)
  .presence-indicator__dot {
    @apply flex-shrink-0;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:38:5
Warn: Unknown at rule @apply (css)
  .typing-indicator {
    @apply flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:42:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__content {
    @apply flex items-center gap-2;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:46:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__dots {
    @apply flex items-center gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:50:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__dot {
    @apply w-1.5 h-1.5 bg-muted-foreground rounded-full;
    animation: typing-pulse 1.4s infinite ease-in-out;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:63:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__text {
    @apply text-xs;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:68:7
Warn: Unknown at rule @apply (css)
    0%, 80%, 100% {
      @apply opacity-30 scale-75;
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:71:7
Warn: Unknown at rule @apply (css)
    40% {
      @apply opacity-100 scale-100;
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:86:5
Warn: Unknown at rule @apply (css)
  .websocket-status {
    @apply flex flex-col gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:90:5
Warn: Unknown at rule @apply (css)
  .websocket-status__details {
    @apply flex flex-col gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:94:5
Warn: Unknown at rule @apply (css)
  .websocket-status__error {
    @apply flex items-center gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:99:5
Warn: Unknown at rule @apply (css)
  .websocket-status__subscriptions {
    @apply flex items-center;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:94:5
Warn: Unknown at rule @apply (css)
  .chat-bubble {
    @apply max-w-[85%] cursor-pointer p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:98:5
Warn: Unknown at rule @apply (css)
  .row--agent-block {
    @apply items-center flex text-left pb-2 text-xs;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:102:5
Warn: Unknown at rule @apply (css)
  .agent--name {
    @apply font-medium ml-1 text-gray-900;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:106:5
Warn: Unknown at rule @apply (css)
  .company--name {
    @apply text-gray-600 ml-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:110:5
Warn: Unknown at rule @apply (css)
  .message-content {
    @apply text-sm text-gray-800 leading-relaxed;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:114:5
Warn: Unknown at rule @apply (css)
  .message-content :global(br) {
    @apply block mb-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:125:5
Warn: Unknown at rule @apply (css)
  .unread-messages {
    @apply pb-2;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:130:5
Warn: Unknown at rule @apply (css)
    transition: all 0.3s cubic-bezier(0.17, 0.67, 0.83, 0.67);
    @apply bg-transparent border-none border-0 font-semibold text-base ml-1 py-0 pl-0 pr-2.5 hover:brightness-75 hover:translate-x-1;
    color: var(--widget-color, #1f93ff);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:136:5
Warn: Unknown at rule @apply (css)
    transition: all 0.3s cubic-bezier(0.17, 0.67, 0.83, 0.67);
    @apply bg-gray-100 text-gray-800 hover:bg-gray-200 border-none border-0 font-medium text-xs rounded-2xl mb-3 px-3 py-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:140:5
Warn: Unknown at rule @apply (css)
  .button {
    @apply cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:261:5
Warn: Unknown at rule @apply (css)
  .widget-app {
    @apply w-full h-full;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:267:5
Warn: Unknown at rule @apply (css)
  .home-view {
    @apply p-4;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:271:5
Warn: Unknown at rule @apply (css)
  .campaign-notification {
    @apply mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:275:5
Warn: Unknown at rule @apply (css)
  .campaign-notification button {
    @apply mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700;
  }

====================================
svelte-check found 4 errors and 27 warnings in 8 files
 ELIFECYCLE  Command failed with exit code 1.
