<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';

  interface CustomAttribute {
    id: string;
    attribute_key: string;
    attribute_display_name: string;
    attribute_display_type: 'text' | 'number' | 'date' | 'link' | 'list' | 'checkbox';
    value?: string | number | boolean;
  }

  let {
    attributes = [],
    values = $bindable<Record<string, any>>({}),
    readonly = false,
    class: className = '',
    ...restProps
  }: {
    attributes?: CustomAttribute[];
    values?: Record<string, any>;
    readonly?: boolean;
    class?: string;
  } = $props();
</script>

<div class={cn('space-y-4', className)} {...restProps}>
  {#if attributes.length === 0}
    <p class="text-sm text-muted-foreground">No custom attributes defined</p>
  {:else}
    {#each attributes as attr}
      <div class="flex flex-col gap-1.5">
        <label for={attr.attribute_key} class="text-sm font-medium">
          {attr.attribute_display_name}
        </label>
        
        {#if attr.attribute_display_type === 'text'}
          <Input
            id={attr.attribute_key}
            type="text"
            bind:value={values[attr.attribute_key]}
            disabled={readonly}
            placeholder={`Enter ${attr.attribute_display_name.toLowerCase()}`}
          />
        {:else if attr.attribute_display_type === 'number'}
          <Input
            id={attr.attribute_key}
            type="number"
            bind:value={values[attr.attribute_key]}
            disabled={readonly}
          />
        {:else if attr.attribute_display_type === 'date'}
          <Input
            id={attr.attribute_key}
            type="date"
            bind:value={values[attr.attribute_key]}
            disabled={readonly}
          />
        {:else if attr.attribute_display_type === 'link'}
          <div class="flex gap-2">
            <Input
              id={attr.attribute_key}
              type="url"
              bind:value={values[attr.attribute_key]}
              disabled={readonly}
              placeholder="https://"
              class="flex-1"
            />
            {#if values[attr.attribute_key] && !readonly}
              <Button
                variant="outline"
                size="sm"
                onclick={() => window.open(values[attr.attribute_key], '_blank')}
              >
                Open
              </Button>
            {/if}
          </div>
        {:else if attr.attribute_display_type === 'checkbox'}
          <label class="flex items-center gap-2">
            <input
              type="checkbox"
              bind:checked={values[attr.attribute_key]}
              disabled={readonly}
              class="w-4 h-4 rounded border"
            />
            <span class="text-sm">Yes</span>
          </label>
        {:else}
          <Input
            id={attr.attribute_key}
            type="text"
            bind:value={values[attr.attribute_key]}
            disabled={readonly}
          />
        {/if}
      </div>
    {/each}
  {/if}
</div>
