<script lang="ts" module>
  import type { DateValue } from '@internationalized/date';
  
  export type CalendarProps = {
    value?: DateValue | DateValue[];
    placeholder?: DateValue;
    type?: 'single' | 'multiple';
    disabled?: boolean;
    readonly?: boolean;
    minValue?: DateValue;
    maxValue?: DateValue;
    isDateDisabled?: (date: DateValue) => boolean;
    isDateUnavailable?: (date: DateValue) => boolean;
    locale?: string;
    numberOfMonths?: number;
    pagedNavigation?: boolean;
    weekStartsOn?: 0 | 1 | 2 | 3 | 4 | 5 | 6;
    fixedWeeks?: boolean;
    class?: string;
  };
</script>

<script lang="ts">
  import { Calendar as CalendarPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  
  let {
    value = $bindable(),
    placeholder,
    type = 'single',
    disabled = false,
    readonly = false,
    minValue,
    maxValue,
    isDateDisabled,
    isDateUnavailable,
    locale = 'en',
    numberOfMonths = 1,
    pagedNavigation = false,
    weekStartsOn = 0,
    fixedWeeks = false,
    class: className,
    ...restProps
  }: CalendarProps = $props();
</script>

<CalendarPrimitive.Root
  bind:value={value as never}
  {placeholder}
  {disabled}
  {readonly}
  {minValue}
  {maxValue}
  {isDateDisabled}
  {isDateUnavailable}
  {locale}
  {numberOfMonths}
  {pagedNavigation}
  {weekStartsOn}
  {fixedWeeks}
  {type}
  class={cn('p-3', className)}
  {...restProps}
>
  {#snippet children({ months, weekdays })}
    <div>
      <CalendarPrimitive.Header class="flex w-full items-center justify-between gap-1">
        <CalendarPrimitive.PrevButton
          class={cn(
            'inline-flex size-10 items-center justify-center rounded-md border border-input bg-background',
            'hover:bg-accent hover:text-accent-foreground',
            'disabled:pointer-events-none disabled:opacity-50'
          )}
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <polyline points="15 18 9 12 15 6" />
          </svg>
        </CalendarPrimitive.PrevButton>
        <CalendarPrimitive.Heading class="text-sm font-medium" />
        <CalendarPrimitive.NextButton
          class={cn(
            'inline-flex size-10 items-center justify-center rounded-md border border-input bg-background',
            'hover:bg-accent hover:text-accent-foreground',
            'disabled:pointer-events-none disabled:opacity-50'
          )}
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <polyline points="9 18 15 12 9 6" />
          </svg>
        </CalendarPrimitive.NextButton>
      </CalendarPrimitive.Header>
      <div class="mt-4 flex flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
        {#each months as month}
          <CalendarPrimitive.Grid class="w-full border-collapse space-y-1">
            <CalendarPrimitive.GridHead>
              <CalendarPrimitive.GridRow class="mb-1 flex w-full justify-between">
                {#each weekdays as day}
                  <CalendarPrimitive.HeadCell class="w-10 rounded-md text-[0.8rem] font-normal text-muted-foreground">
                    <div>{day.slice(0, 2)}</div>
                  </CalendarPrimitive.HeadCell>
                {/each}
              </CalendarPrimitive.GridRow>
            </CalendarPrimitive.GridHead>
            <CalendarPrimitive.GridBody>
              {#each month.weeks as weekDates}
                <CalendarPrimitive.GridRow class="mt-2 flex w-full">
                  {#each weekDates as date}
                    <CalendarPrimitive.Cell {date} month={month.value} class="relative size-10 p-0 text-center text-sm focus-within:relative focus-within:z-20">
                      <CalendarPrimitive.Day
                        class={cn(
                          'inline-flex size-10 items-center justify-center rounded-md p-0 text-sm font-normal',
                          'ring-offset-background transition-colors',
                          'hover:bg-accent hover:text-accent-foreground',
                          'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                          'disabled:pointer-events-none disabled:opacity-50',
                          'aria-selected:opacity-100',
                          '[&[data-today]:not([data-selected])]:bg-accent [&[data-today]:not([data-selected])]:text-accent-foreground',
                          '[&[data-selected]]:bg-primary [&[data-selected]]:text-primary-foreground',
                          '[&[data-selected]]:hover:bg-primary [&[data-selected]]:hover:text-primary-foreground',
                          '[&[data-selected]]:focus:bg-primary [&[data-selected]]:focus:text-primary-foreground',
                          '[&[data-outside-visible-months]]:text-muted-foreground [&[data-outside-visible-months]]:opacity-50',
                          '[&[data-disabled]]:text-muted-foreground [&[data-disabled]]:opacity-50'
                        )}
                      />
                    </CalendarPrimitive.Cell>
                  {/each}
                </CalendarPrimitive.GridRow>
              {/each}
            </CalendarPrimitive.GridBody>
          </CalendarPrimitive.Grid>
        {/each}
      </div>
    </div>
  {/snippet}
</CalendarPrimitive.Root>
