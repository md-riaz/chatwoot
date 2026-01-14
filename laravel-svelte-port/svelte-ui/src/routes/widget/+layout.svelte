<script lang="ts">
  import { onMount } from 'svelte';
  import type { Snippet } from 'svelte';
  import { widgetConfigStore } from '$lib/widget/stores/config.svelte';
  import { setWebsiteToken } from '$lib/widget/api/client';
  import { initI18n } from '$lib/i18n';
  import { initAudioNotifications } from '$lib/widget/utils/audio';
  import { listenToParentEvents, notifyWidgetReady } from '$lib/widget/utils/iframe';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();

  // Get widget token from URL params
  const urlParams = new URLSearchParams(
    typeof window !== 'undefined' ? window.location.search : ''
  );
  const websiteToken = urlParams.get('website_token') || '';

  onMount(() => {
    // Set website token for API calls
    if (websiteToken) {
      setWebsiteToken(websiteToken);

      // Load widget configuration (would come from API)
      const mockConfig = {
        websiteToken,
        widgetColor: '#1f93ff',
        position: 'right' as const,
        locale: 'en',
        enabledFeatures: [],
        preChatFormEnabled: false,
        preChatFormOptions: {
          requireEmail: false,
          requireName: false,
          requirePhoneNumber: false,
          preChatMessage: '',
        },
        replyTime: '2 hours',
        businessName: 'Support Team',
        businessDescription: 'We are here to help!',
        businessHours: {
          enabled: false,
          timezone: 'UTC',
          schedule: {},
        },
      };

      widgetConfigStore.setConfig(mockConfig);
    }

    // Initialize async operations
    (async () => {
      // Initialize i18n
      await initI18n();

      // Initialize audio notifications
      await initAudioNotifications();

      // Notify parent that widget is ready
      notifyWidgetReady();
    })();

    // Listen to parent window events
    const cleanup = listenToParentEvents((event) => {
      if (event.event === 'toggle') {
        widgetConfigStore.toggle();
      }
    });

    return cleanup;
  });

  const widgetColor = $derived(widgetConfigStore.widgetColor);
</script>

<div class="widget-container" style:--widget-color={widgetColor}>
  {@render children()}
</div>

<style>
  .widget-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial,
      sans-serif;
    font-size: 14px;
    color: #1f2937;
    height: 100vh;
    overflow: hidden;
  }

  :global(*) {
    box-sizing: border-box;
  }
</style>
