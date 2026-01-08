<script lang="ts">
  import { Spinner } from './index.js';
  
  export let Hst: any;
  
  const sizes = ['sm', 'default', 'lg', 'xl'] as const;
</script>

<Hst.Story title="Primitives/Spinner" icon="lucide:loader-2">
  <Hst.Variant title="Default">
    <div class="flex gap-4 p-4">
      <Spinner />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sizes">
    <div class="flex items-center gap-4 p-4">
      {#each sizes as size}
        <div class="text-center">
          <Spinner {size} />
          <p class="text-xs text-muted-foreground mt-2">{size}</p>
        </div>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Colors">
    <div class="flex items-center gap-4 p-4">
      <Spinner class="text-primary" />
      <Spinner class="text-destructive" />
      <Spinner class="text-success" />
      <Spinner class="text-warning" />
      <Spinner class="text-muted-foreground" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Loading States">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div class="flex items-center gap-2">
        <Spinner size="sm" />
        <span class="text-sm">Loading messages...</span>
      </div>
      <div class="h-32 border rounded-lg flex items-center justify-center">
        <div class="text-center">
          <Spinner size="lg" class="mx-auto" />
          <p class="text-sm text-muted-foreground mt-2">Loading content</p>
        </div>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
