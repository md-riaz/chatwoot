<script lang="ts">
  import { Checkbox } from './index.js';
  
  export let Hst: any;
  
  let terms = $state(false);
  let newsletter = $state(true);
  let indeterminate = $state<boolean | 'indeterminate'>('indeterminate');
</script>

<Hst.Story title="Primitives/Checkbox" icon="lucide:check-square">
  <Hst.Variant title="Default">
    <div class="flex items-center gap-2 p-4">
      <Checkbox bind:checked={terms} />
      <span class="text-sm">{terms ? 'Checked' : 'Unchecked'}</span>
    </div>
  </Hst.Variant>

  <Hst.Variant title="States">
    <div class="flex flex-col gap-4 p-4">
      <div class="flex items-center gap-2">
        <Checkbox checked={true} />
        <span class="text-sm">Checked</span>
      </div>
      <div class="flex items-center gap-2">
        <Checkbox checked={false} />
        <span class="text-sm">Unchecked</span>
      </div>
      <div class="flex items-center gap-2">
        <Checkbox bind:checked={indeterminate} />
        <span class="text-sm">Indeterminate</span>
      </div>
      <div class="flex items-center gap-2">
        <Checkbox disabled />
        <span class="text-sm text-muted-foreground">Disabled</span>
      </div>
      <div class="flex items-center gap-2">
        <Checkbox checked={true} disabled />
        <span class="text-sm text-muted-foreground">Disabled Checked</span>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Label">
    <div class="flex flex-col gap-4 p-4">
      <label class="flex items-center gap-2 cursor-pointer">
        <Checkbox bind:checked={terms} />
        <span class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
          Accept terms and conditions
        </span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <Checkbox bind:checked={newsletter} />
        <span class="text-sm font-medium leading-none">
          Subscribe to newsletter
        </span>
      </label>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Form Example">
    <div class="p-4 max-w-sm space-y-4">
      <div class="space-y-2">
        <h4 class="text-sm font-medium">Notification Preferences</h4>
        <div class="space-y-2">
          <label class="flex items-start gap-2 cursor-pointer">
            <Checkbox class="mt-0.5" />
            <div>
              <span class="text-sm font-medium">Email notifications</span>
              <p class="text-sm text-muted-foreground">Receive emails for new messages</p>
            </div>
          </label>
          <label class="flex items-start gap-2 cursor-pointer">
            <Checkbox class="mt-0.5" checked={true} />
            <div>
              <span class="text-sm font-medium">Push notifications</span>
              <p class="text-sm text-muted-foreground">Receive push notifications on your device</p>
            </div>
          </label>
          <label class="flex items-start gap-2 cursor-pointer">
            <Checkbox class="mt-0.5" />
            <div>
              <span class="text-sm font-medium">SMS notifications</span>
              <p class="text-sm text-muted-foreground">Receive SMS for urgent messages</p>
            </div>
          </label>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Select All Pattern">
    <div class="p-4 max-w-sm">
      <div class="space-y-3">
        <label class="flex items-center gap-2 cursor-pointer border-b pb-2">
          <Checkbox checked="indeterminate" />
          <span class="text-sm font-medium">Select All</span>
        </label>
        <div class="pl-4 space-y-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <Checkbox checked={true} />
            <span class="text-sm">Item 1</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <Checkbox checked={true} />
            <span class="text-sm">Item 2</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <Checkbox checked={false} />
            <span class="text-sm">Item 3</span>
          </label>
        </div>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
