<script lang="ts">
  /**
   * Widget Preview Head Component
   * Displays the header section of the chat widget preview
   */
  interface Props {
    logo?: string;
    websiteName: string;
    isOnline?: boolean;
    replyTime?: string;
    welcomeHeading?: string;
    welcomeTagline?: string;
    isDefaultScreen?: boolean;
  }

  let {
    logo,
    websiteName,
    isOnline = true,
    replyTime = 'Typically replies in a few hours',
    welcomeHeading,
    welcomeTagline,
    isDefaultScreen = true,
  }: Props = $props();

  const showDefaultScreen = $derived(
    isDefaultScreen && (welcomeHeading?.length || welcomeTagline?.length)
  );
</script>

<div
  class="rounded-t-lg flex-shrink-0 transition-[max-height] duration-300"
  class:bg-slate-100={showDefaultScreen}
  class:dark:bg-slate-900={showDefaultScreen}
  class:px-4={showDefaultScreen}
  class:py-5={showDefaultScreen}
  class:p-4={!showDefaultScreen}
>
  <div class="relative top-px">
    <div class="flex items-center justify-start">
      {#if logo}
        <img
          src={logo}
          alt={websiteName}
          class="mr-2 rounded-full logo {showDefaultScreen ? 'h-12 w-12 mb-2' : 'h-8 w-8 mb-1'}"
        />
      {/if}
      {#if !showDefaultScreen}
        <div>
          <div class="flex items-center justify-start gap-1">
            <span class="text-base font-medium leading-3 text-slate-900 dark:text-slate-100">
              {websiteName}
            </span>
            {#if isOnline}
              <div class="w-2 h-2 bg-teal-500 rounded-full"></div>
            {/if}
          </div>
          <span class="mt-1 text-xs text-slate-600 dark:text-slate-400">
            {replyTime}
          </span>
        </div>
      {/if}
    </div>
    {#if showDefaultScreen}
      <div class="overflow-auto max-h-60">
        {#if welcomeHeading}
          <h2 class="mb-2 text-2xl break-words text-slate-900 dark:text-slate-100">
            {welcomeHeading}
          </h2>
        {/if}
        {#if welcomeTagline}
          <p class="text-sm break-words text-slate-600 dark:text-slate-400">
            {@html welcomeTagline}
          </p>
        {/if}
      </div>
    {/if}
  </div>
</div>
