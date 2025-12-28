<script lang="ts" context="module">
  import type { DateValue } from '@internationalized/date';
  
  export type CalendarProps = {
    value?: DateValue;
    placeholder?: DateValue;
    multiple?: boolean;
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
  import { RangeCalendar as RangeCalendarPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  
  let {
    value = $bindable(),
    placeholder,
    multiple = false,
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

<RangeCalendarPrimitive.Root
  bind:value
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
  class={cn('p-3', className)}
  {...restProps}
  let:months
  let:weekdays
>
  <RangeCalendarPrimitive.Header class="flex w-full items-center justify-between gap-1">
    <RangeCalendarPrimitive.PrevButton
      class={cn(
        'inline-flex size-10 items-center justify-center rounded-md border border-input bg-background',
        'hover:bg-accent hover:text-accent-foreground',
        'disabled:pointer-events-none disabled:opacity-50'
      )}
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="size-4"
      >
        <polyline points="15 18 9 12 15 6" />
      </svg>
    </RangeCalendarPrimitive.PrevButton>
    <RangeCalendarPrimitive.Heading class="text-sm font-medium" />
    <RangeCalendarPrimitive.NextButton
      class={cn(
        'inline-flex size-10 items-center justify-center rounded-md border border-input bg-background',
        'hover:bg-accent hover:text-accent-foreground',
        'disabled:pointer-events-none disabled:opacity-50'
      )}
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="size-4"
      >
        <polyline points="9 18 15 12 9 6" />
      </svg>
    </RangeCalendarPrimitive.NextButton>
  </RangeCalendarPrimitive.Header>
  <div class="mt-4 flex flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
    {#each months as month}
      <RangeCalendarPrimitive.Grid class="w-full border-collapse space-y-1">
        <RangeCalendarPrimitive.GridHead>
          <RangeCalendarPrimitive.GridRow class="mb-1 flex w-full justify-between">
            {#each weekdays as day}
              <RangeCalendarPrimitive.HeadCell
                class="w-10 rounded-md text-[0.8rem] font-normal text-muted-foreground"
              >
                <div>{day.slice(0, 2)}</div>
              </RangeCalendarPrimitive.HeadCell>
            {/each}
          </RangeCalendarPrimitive.GridRow}
        </RangeCalendarPrimitive.GridHead>
        <RangeCalendarPrimitive.GridBody>
          {#each month.weeks as weekDates}
            <RangeCalendarPrimitive.GridRow class="mt-2 flex w-full">
              {#each weekDates as date}
                <RangeCalendarPrimitive.Cell
                  {date}
                  class="relative size-10 p-0 text-center text-sm focus-within:relative focus-within:z-20"
                >
                  <RangeCalendarPrimitive.Day
                    {date}
                    month={month.value}
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
                </RangeCalendarPrimitive.Cell>
              {/each}
            </RangeCalendarPrimitive.GridRow>
          {/each}
        </RangeCalendarPrimitive.GridBody>
      </RangeCalendarPrimitive.Grid>
    {/each}
  </div>
</RangeCalendarPrimitive.Root>
