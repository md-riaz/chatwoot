<script lang="ts">
  /**
   * Widget Preview Component
   * Complete chat widget preview with customizable appearance
   */
  import { RadioGroup, RadioGroupItem } from '$lib/components/ui/radio-group';
  import { Label } from '$lib/components/ui/label';
  import { MessageCircle, X } from 'lucide-svelte';
  import WidgetHead from './WidgetHead.svelte';
  import WidgetBody from './WidgetBody.svelte';
  import WidgetFooter from './WidgetFooter.svelte';

  interface Props {
    welcomeHeading?: string;
    welcomeTagline?: string;
    websiteName: string;
    logo?: string;
    isOnline?: boolean;
    replyTime?: string;
    color?: string;
    widgetBubblePosition?: string;
    widgetBubbleLauncherTitle?: string;
    widgetBubbleType?: string;
  }

  let {
    welcomeHeading = 'Welcome to our support!',
    welcomeTagline = 'We are here to help you with any questions you might have.',
    websiteName,
    logo,
    isOnline = true,
    replyTime = 'Typically replies in a few hours',
    color = '#1f93ff',
    widgetBubblePosition = 'right',
    widgetBubbleLauncherTitle = 'Chat with us',
    widgetBubbleType = 'standard',
  }: Props = $props();

  let isDefaultScreen = $state(true);
  let isWidgetVisible = $state(true);
  let selectedScreen = $state('default');

  function handleScreenChange(value: string) {
    selectedScreen = value;
    isDefaultScreen = value === 'default';
  }

  function toggleWidget() {
    isWidgetVisible = !isWidgetVisible;
  }
</script>

<div class="flex flex-col h-full">
  <!-- Screen selector -->
  <div class="mb-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-lg">
    <RadioGroup value={selectedScreen} onValueChange={handleScreenChange}>
      <div class="flex items-center space-x-2">
        <RadioGroupItem value="default" id="screen-default" />
        <Label for="screen-default">Default Screen</Label>
      </div>
      <div class="flex items-center space-x-2">
        <RadioGroupItem value="chat" id="screen-chat" />
        <Label for="screen-chat">Chat Screen</Label>
      </div>
    </RadioGroup>
  </div>

  <!-- Widget preview container -->
  <div class="flex-1 relative bg-slate-200 dark:bg-slate-800 rounded-lg p-8 overflow-hidden">
    <!-- Widget bubble launcher -->
    <button
      onclick={toggleWidget}
      class="absolute bottom-6 {widgetBubblePosition === 'left' ? 'left-6' : 'right-6'} w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-white transition-transform hover:scale-110"
      style="background-color: {color}"
    >
      {#if isWidgetVisible}
        <X class="h-6 w-6" />
      {:else}
        <MessageCircle class="h-6 w-6" />
      {/if}
    </button>

    <!-- Widget window -->
    {#if isWidgetVisible}
      <div
        class="absolute bottom-24 {widgetBubblePosition === 'left' ? 'left-6' : 'right-6'} w-96 h-128 bg-white dark:bg-slate-950 rounded-lg shadow-2xl flex flex-col overflow-hidden transition-all duration-300"
        style="border-top: 4px solid {color}"
      >
        <WidgetHead
          {logo}
          {websiteName}
          {isOnline}
          {replyTime}
          {welcomeHeading}
          {welcomeTagline}
          {isDefaultScreen}
        />
        <WidgetBody {isDefaultScreen} />
        <WidgetFooter {isDefaultScreen} />
      </div>
    {/if}

    <!-- Bubble launcher title -->
    {#if !isWidgetVisible && widgetBubbleLauncherTitle}
      <div
        class="absolute bottom-24 {widgetBubblePosition === 'left' ? 'left-6' : 'right-6'} bg-white dark:bg-slate-900 px-4 py-2 rounded-lg shadow-lg text-sm font-medium text-slate-900 dark:text-slate-100"
      >
        {widgetBubbleLauncherTitle}
      </div>
    {/if}
  </div>
</div>
