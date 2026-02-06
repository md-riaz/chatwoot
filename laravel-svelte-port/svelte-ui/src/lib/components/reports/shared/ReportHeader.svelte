<script lang="ts">
  interface Props {
    headerTitle: string;
    headerDescription?: string;
    hasBackButton?: boolean;
  }
  
  let { 
    headerTitle, 
    headerDescription = '', 
    hasBackButton = false 
  }: Props = $props();
</script>

<section class="flex flex-col gap-1 pt-10 pb-5">
  {#if hasBackButton}
    <div>
      <!-- BackButton component would go here if needed -->
    </div>
  {/if}
  <div class="flex justify-between w-full gap-5">
    <div class="flex flex-col gap-2">
      <div>
        <span class="text-xl font-medium text-slate-900 dark:text-slate-100">
          {headerTitle}
        </span>
        {#if headerDescription}
          <p class="text-slate-900 dark:text-slate-100 mt-2">
            {headerDescription}
          </p>
        {/if}
      </div>
    </div>
    <div class="flex-shrink-0">
      <slot />
    </div>
  </div>
</section>