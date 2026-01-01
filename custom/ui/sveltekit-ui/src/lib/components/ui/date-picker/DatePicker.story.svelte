<script lang="ts">
  import { DatePicker } from './index.js';
  import { today, getLocalTimeZone } from '@internationalized/date';
  
  export let Hst: any;
  
  let selectedDate = $state(undefined);
  let dateWithDefault = $state(today(getLocalTimeZone()));
  let minDate = today(getLocalTimeZone());
  let maxDate = today(getLocalTimeZone()).add({ months: 2 });
</script>

<Hst.Story title="Primitives/DatePicker" icon="lucide:calendar-days">
  <Hst.Variant title="Default">
    <div class="flex justify-center p-4">
      <div class="w-80">
        <DatePicker bind:value={selectedDate} />
        {#if selectedDate}
          <p class="mt-2 text-sm text-muted-foreground">
            Selected: {selectedDate.toString()}
          </p>
        {/if}
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Default Value">
    <div class="flex justify-center p-4">
      <div class="w-80">
        <DatePicker bind:value={dateWithDefault} />
        <p class="mt-2 text-sm text-muted-foreground">
          Selected: {dateWithDefault?.toString() || 'None'}
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Placeholder">
    <div class="flex justify-center p-4">
      <div class="w-80">
        <DatePicker placeholder="Select your birthday" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled State">
    <div class="flex justify-center p-4">
      <div class="w-80">
        <DatePicker disabled placeholder="Disabled picker" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Min/Max Dates">
    <div class="flex justify-center p-4">
      <div class="w-80">
        <DatePicker
          minValue={minDate}
          maxValue={maxDate}
          placeholder="Select date (next 2 months only)"
        />
        <p class="mt-2 text-sm text-muted-foreground">
          Range: Today to {maxDate.toString()}
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Form Integration">
    <div class="flex justify-center p-4">
      <div class="w-80 space-y-4">
        <div>
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            Start Date
          </label>
          <DatePicker placeholder="Select start date" class="mt-2" />
        </div>
        <div>
          <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
            End Date
          </label>
          <DatePicker placeholder="Select end date" class="mt-2" />
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Full Width">
    <div class="p-4">
      <DatePicker class="w-full" placeholder="Full width date picker" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="In a Card">
    <div class="flex justify-center p-4">
      <div class="w-96 rounded-lg border bg-card p-6 text-card-foreground shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Schedule Meeting</h3>
        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium mb-2 block">Meeting Date</label>
            <DatePicker placeholder="Choose a date" />
          </div>
          <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full">
            Schedule Meeting
          </button>
        </div>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
