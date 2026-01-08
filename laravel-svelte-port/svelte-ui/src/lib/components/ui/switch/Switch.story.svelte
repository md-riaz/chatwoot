<script lang="ts">
  import { Switch } from './index.js';
  
  export let Hst: any;
  
  let checked = $state(false);
  let notifications = $state(true);
  let marketing = $state(false);
</script>

<Hst.Story title="Primitives/Switch" icon="lucide:toggle-left">
  <Hst.Variant title="Default">
    <div class="flex items-center gap-4 p-4">
      <Switch bind:checked />
      <span class="text-sm">{checked ? 'On' : 'Off'}</span>
    </div>
  </Hst.Variant>

  <Hst.Variant title="States">
    <div class="flex flex-col gap-4 p-4">
      <div class="flex items-center gap-4">
        <Switch checked={true} />
        <span class="text-sm">Checked</span>
      </div>
      <div class="flex items-center gap-4">
        <Switch checked={false} />
        <span class="text-sm">Unchecked</span>
      </div>
      <div class="flex items-center gap-4">
        <Switch disabled />
        <span class="text-sm text-muted-foreground">Disabled</span>
      </div>
      <div class="flex items-center gap-4">
        <Switch checked={true} disabled />
        <span class="text-sm text-muted-foreground">Disabled Checked</span>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Settings Example">
    <div class="p-4 max-w-md space-y-4">
      <div class="flex items-center justify-between rounded-lg border p-4">
        <div class="space-y-0.5">
          <p class="text-sm font-medium">Push Notifications</p>
          <p class="text-sm text-muted-foreground">Receive push notifications on your device</p>
        </div>
        <Switch bind:checked={notifications} />
      </div>
      <div class="flex items-center justify-between rounded-lg border p-4">
        <div class="space-y-0.5">
          <p class="text-sm font-medium">Marketing Emails</p>
          <p class="text-sm text-muted-foreground">Receive emails about new features and updates</p>
        </div>
        <Switch bind:checked={marketing} />
      </div>
      <div class="flex items-center justify-between rounded-lg border p-4">
        <div class="space-y-0.5">
          <p class="text-sm font-medium">Two-Factor Auth</p>
          <p class="text-sm text-muted-foreground">Required for enterprise accounts</p>
        </div>
        <Switch checked={true} disabled />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Labels">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <label class="flex items-center gap-3 cursor-pointer">
        <Switch />
        <span class="text-sm font-medium">Enable dark mode</span>
      </label>
      <label class="flex items-center gap-3 cursor-pointer">
        <Switch />
        <span class="text-sm font-medium">Show online status</span>
      </label>
      <label class="flex items-center gap-3 cursor-pointer">
        <Switch />
        <span class="text-sm font-medium">Sound notifications</span>
      </label>
    </div>
  </Hst.Variant>
</Hst.Story>
