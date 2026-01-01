<script lang="ts">
  import { Input } from './index.js';
  
  export let Hst: any;
  
  let value = $state('');
  let emailValue = $state('');
  let passwordValue = $state('');
</script>

<Hst.Story title="Primitives/Input" icon="lucide:text-cursor-input">
  <Hst.Variant title="Default">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <Input placeholder="Enter text..." bind:value />
      <p class="text-sm text-muted-foreground">Value: {value}</p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Types">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div>
        <label class="text-sm font-medium mb-1 block">Text</label>
        <Input type="text" placeholder="Text input" />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Email</label>
        <Input type="email" placeholder="email@example.com" bind:value={emailValue} />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Password</label>
        <Input type="password" placeholder="Enter password" bind:value={passwordValue} />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Number</label>
        <Input type="number" placeholder="0" />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Search</label>
        <Input type="search" placeholder="Search..." />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="States">
    <div class="flex flex-col gap-4 p-4 max-w-sm">
      <div>
        <label class="text-sm font-medium mb-1 block">Default</label>
        <Input placeholder="Default state" />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Disabled</label>
        <Input placeholder="Disabled" disabled />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Read-only</label>
        <Input value="Read-only value" readonly />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">With value</label>
        <Input value="Pre-filled value" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Labels and Descriptions">
    <div class="flex flex-col gap-6 p-4 max-w-sm">
      <div class="space-y-2">
        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
          Username
        </label>
        <Input placeholder="@username" />
        <p class="text-sm text-muted-foreground">
          This is your public display name.
        </p>
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium leading-none">
          Email <span class="text-destructive">*</span>
        </label>
        <Input type="email" placeholder="email@example.com" required />
        <p class="text-sm text-muted-foreground">
          We'll never share your email with anyone else.
        </p>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
