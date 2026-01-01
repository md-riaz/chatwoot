<script lang="ts">
  import { Button } from './index.js';
  
  export let Hst: any;
  
  const variants = ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link', 'success', 'warning', 'info'] as const;
  const sizes = ['default', 'sm', 'lg', 'icon'] as const;
</script>

<Hst.Story title="Primitives/Button" icon="lucide:mouse-pointer-click">
  <Hst.Variant title="Default">
    <div class="flex flex-wrap gap-4 p-4">
      <Button>Button</Button>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Variants">
    <div class="flex flex-wrap gap-2 p-4">
      {#each variants as variant}
        <Button {variant}>{variant}</Button>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sizes">
    <div class="flex flex-wrap items-center gap-2 p-4">
      {#each sizes as size}
        <Button {size}>{size === 'icon' ? '🔔' : size}</Button>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled">
    <div class="flex flex-wrap gap-2 p-4">
      <Button disabled>Disabled</Button>
      <Button variant="outline" disabled>Disabled Outline</Button>
      <Button variant="destructive" disabled>Disabled Destructive</Button>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Icons">
    <div class="flex flex-wrap gap-2 p-4">
      <Button>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Add Item
      </Button>
      <Button variant="outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Download
      </Button>
      <Button variant="secondary">
        Settings
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </Button>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Loading State">
    <div class="flex flex-wrap gap-2 p-4">
      <Button disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
        Loading...
      </Button>
      <Button variant="outline" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
        Please wait
      </Button>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Combination Grid">
    <div class="p-4">
      <table class="w-full border-collapse">
        <thead>
          <tr>
            <th class="p-2 text-left text-sm text-muted-foreground">Variant / Size</th>
            {#each sizes as size}
              <th class="p-2 text-center text-sm text-muted-foreground">{size}</th>
            {/each}
          </tr>
        </thead>
        <tbody>
          {#each variants as variant}
            <tr>
              <td class="p-2 text-sm text-muted-foreground">{variant}</td>
              {#each sizes as size}
                <td class="p-2 text-center">
                  <Button {variant} {size}>{size === 'icon' ? '★' : variant.slice(0, 3)}</Button>
                </td>
              {/each}
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </Hst.Variant>
</Hst.Story>
