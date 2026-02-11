<script lang="ts">
  import { cn } from "$lib/utils";

  let { 
    title, 
    description, 
    withBorder = false, 
    hideContent = false, 
    beta = false,
    children,
    headerActions
  }: {
    title: string;
    description: string;
    withBorder?: boolean;
    hideContent?: boolean;
    beta?: boolean;
    children?: any;
    headerActions?: any;
  } = $props();
</script>

<section
  class={cn(
    "grid grid-cols-1 pt-8 gap-5",
    withBorder && "border-t border-border",
    !hideContent && "pb-8"
  )}
>
  <header class="grid grid-cols-4">
    <div class="col-span-3">
      <h4 class="text-lg font-medium text-foreground flex items-center gap-2">
        {title}
        {#if beta}
          <div
            class="text-xs uppercase text-primary border border-primary leading-none rounded-lg px-1 py-0.5"
            title="Beta Feature"
          >
            Beta
          </div>
        {/if}
      </h4>
      <p class="text-muted-foreground text-sm mt-2">
        {description}
      </p>
    </div>
    <div class="col-span-1">
      {@render headerActions?.()}
    </div>
  </header>
  <div
    class={cn(
      "transition-[height] duration-300 ease-in-out text-foreground",
      hideContent ? "overflow-hidden h-0" : "h-auto"
    )}
  >
    {@render children?.()}
  </div>
</section>
