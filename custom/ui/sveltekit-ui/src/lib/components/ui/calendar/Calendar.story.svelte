<script lang="ts">
  import { Calendar } from './index.js';
  import { today, getLocalTimeZone } from '@internationalized/date';
  
  export let Hst: any;
  
  let selectedDate = $state(today(getLocalTimeZone()));
  let rangeStart = $state(today(getLocalTimeZone()));
  let rangeEnd = $state(today(getLocalTimeZone()).add({ days: 7 }));
</script>

<Hst.Story title="Primitives/Calendar" icon="lucide:calendar">
  <Hst.Variant title="Default">
    <div class="flex justify-center p-4">
      <div class="rounded-md border">
        <Calendar />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Selected Date">
    <div class="flex flex-col gap-4 p-4">
      <div class="rounded-md border w-fit">
        <Calendar bind:value={selectedDate} />
      </div>
      <div class="text-sm text-muted-foreground">
        Selected: {selectedDate?.toString() || 'None'}
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Multiple Months">
    <div class="flex justify-center p-4">
      <div class="rounded-md border">
        <Calendar numberOfMonths={2} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled State">
    <div class="flex justify-center p-4">
      <div class="rounded-md border opacity-50">
        <Calendar disabled />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Min/Max Dates">
    <div class="flex justify-center p-4">
      <div class="rounded-md border">
        <Calendar
          minValue={today(getLocalTimeZone())}
          maxValue={today(getLocalTimeZone()).add({ months: 2 })}
        />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Week Starts on Monday">
    <div class="flex justify-center p-4">
      <div class="rounded-md border">
        <Calendar weekStartsOn={1} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Fixed Weeks">
    <div class="flex justify-center p-4">
      <div class="rounded-md border">
        <Calendar fixedWeeks />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Styling">
    <div class="flex justify-center p-4">
      <div class="rounded-md border bg-secondary">
        <Calendar class="bg-secondary" />
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
