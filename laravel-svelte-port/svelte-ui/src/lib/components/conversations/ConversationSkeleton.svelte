<script lang="ts">
  /**
   * ConversationSkeleton - Loading skeleton for conversation items
   */
  
  import * as Skeleton from '$lib/components/ui/skeleton';
  
  interface Props {
    count?: number;
  }
  
  let { count = 3 }: Props = $props();
</script>

<div class="flex flex-col gap-2">
  {#each Array(count) as _, i (i)}
    <div class="flex gap-3 p-3 border-b">
      <!-- Avatar skeleton -->
      <Skeleton.Root class="h-10 w-10 rounded-full shrink-0" />
      
      <!-- Content skeleton -->
      <div class="flex flex-col gap-2 flex-1">
        <!-- Header row -->
        <div class="flex items-center justify-between">
          <Skeleton.Root class="h-4 w-32" />
          <Skeleton.Root class="h-4 w-16" />
        </div>
        
        <!-- Message preview -->
        <Skeleton.Root class="h-3 w-full" />
        <Skeleton.Root class="h-3 w-3/4" />
        
        <!-- Meta row -->
        <div class="flex gap-2">
          <Skeleton.Root class="h-5 w-16 rounded-full" />
          <Skeleton.Root class="h-5 w-20 rounded-full" />
        </div>
      </div>
    </div>
  {/each}
</div>
