<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { DatePicker } from '$lib/components/ui/date-picker';
  import { parseDate, type DateValue } from '@internationalized/date';
  import { Popover, PopoverTrigger, PopoverContent } from '$lib/components/ui/popover';
  import { Calendar } from '$lib/components/ui/calendar';

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

  // Helper function to convert string to DateValue
  function getDateValue(dateStr: string | undefined): DateValue | undefined {
    if (!dateStr) return undefined;
    try {
      return parseDate(dateStr);
    } catch {
      return undefined;
    }
  }

  // Format date for display
  function formatDate(dateStr: string | undefined): string {
    if (!dateStr) return '';
    try {
      const dateValue = parseDate(dateStr);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(new Date(dateValue.toString()));
    } catch {
      return dateStr;
    }
  }
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
          {#snippet dateField()}
            {@const key = attr.attribute_key}
            {(() => {
              let open = $state(false);
              let dateValue = $state(getDateValue(values[key]));
              
              $effect(() => {
                const newDate = getDateValue(values[key]);
                if (newDate?.toString() !== dateValue?.toString()) {
                  dateValue = newDate;
                }
              });
              
              return null;
            })()}
            
            <Popover bind:open>
              <PopoverTrigger>
                {#snippet child({ props }: { props: any })}
                  <Button
                    {...props}
                    variant="outline"
                    class="w-full justify-start text-left font-normal"
                    disabled={readonly}
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="mr-2 size-4"
                    >
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                      <line x1="16" y1="2" x2="16" y2="6" />
                      <line x1="8" y1="2" x2="8" y2="6" />
                      <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    {values[key] ? formatDate(values[key]) : 'Select a date'}
                  </Button>
                {/snippet}
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0" align="start">
                <Calendar
                  value={getDateValue(values[key])}
                  disabled={readonly}
                  readonly={readonly}
                  onvaluechange={(newValue) => {
                    values[key] = newValue?.toString() || '';
                    open = false;
                  }}
                />
              </PopoverContent>
            </Popover>
          {/snippet}
          {@render dateField()}
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
