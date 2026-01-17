<script lang="ts">
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Badge } from '$lib/components/ui/badge';
  import { Avatar, AvatarImage, AvatarFallback } from '$lib/components/ui/avatar';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { Switch } from '$lib/components/ui/switch';
  import { Label } from '$lib/components/ui/label';
  import * as RadioGroup from '$lib/components/ui/radio-group';
  import * as Select from '$lib/components/ui/select';
  import { Plus } from '@lucide/svelte';
  
  // Some components might not be exported from the main barrel or need specific imports if they are complex
  // But generally UI.* should work for those in index.ts

  $: componentName = $page.params.name;

  // Mock data for various components
  const frameworks = [
    { value: "svelte", label: "Svelte" },
    { value: "vue", label: "Vue" },
    { value: "react", label: "React" },
  ];
</script>

<div class="container mx-auto py-10 md:py-16 space-y-10">
  <div class="flex items-center justify-between">
    <div class="space-y-2">
      <Button variant="ghost" size="sm" class="-ml-2 mb-2" onclick={() => goto('/')}>
        &larr; Back to Components
      </Button>
      <h1 class="text-3xl font-bold capitalize">{componentName?.replace(/-/g, ' ') || 'Component'}</h1>
      <p class="text-muted-foreground">
        Preview of the <code class="bg-muted px-1.5 py-0.5 rounded font-mono text-sm">{componentName}</code> component.
      </p>
    </div>
  </div>

  <div class="p-8 border rounded-xl bg-card text-card-foreground shadow-xs min-h-[300px] flex flex-col items-center justify-center gap-8">
    {#if componentName === 'button'}
      <div class="flex flex-col gap-6 items-center">
        <div class="flex gap-2 flex-wrap justify-center">
          <Button>Default</Button>
          <Button variant="secondary">Secondary</Button>
          <Button variant="destructive">Destructive</Button>
          <Button variant="outline">Outline</Button>
          <Button variant="ghost">Ghost</Button>
          <Button variant="link">Link</Button>
        </div>
        <div class="flex gap-2 flex-wrap justify-center">
          <Button size="sm">Small</Button>
          <Button size="default">Default</Button>
          <Button size="lg">Large</Button>
          <Button size="icon"><Plus class="h-4 w-4" /></Button>
        </div>
        <div class="flex gap-2">
           <Button disabled>Disabled</Button>
        </div>
      </div>
    {:else if componentName === 'input'}
      <div class="w-full max-w-md space-y-4">
        <Input type="email" placeholder="Email" />
        <Input type="password" placeholder="Password" />
        <Input type="text" placeholder="Disabled input" disabled />
        <div class="flex w-full items-center space-x-2">
          <Input type="email" placeholder="Enter your email" />
          <Button type="submit">Subscribe</Button>
        </div>
      </div>
    {:else if componentName === 'badge'}
      <div class="flex flex-wrap gap-3 items-center justify-center">
        <Badge>Default</Badge>
        <Badge variant="secondary">Secondary</Badge>
        <Badge variant="outline">Outline</Badge>
        <Badge variant="destructive">Destructive</Badge>
      </div>
    {:else if componentName === 'avatar'}
      <div class="flex gap-4 items-center">
        <Avatar>
          <AvatarImage src="https://github.com/shadcn.png" alt="@shadcn" />
          <AvatarFallback>CN</AvatarFallback>
        </Avatar>
        <Avatar>
          <AvatarImage src="https://github.com/vercel.png" alt="@vercel" />
          <AvatarFallback>VC</AvatarFallback>
        </Avatar>
        <Avatar>
          <AvatarFallback>JD</AvatarFallback>
        </Avatar>
      </div>
    {:else if componentName === 'checkbox'}
      <div class="flex items-center space-x-2">
        <Checkbox />
        <Label
          for="terms"
          class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
        >
          Accept terms and conditions
        </Label>
      </div>
    {:else if componentName === 'switch'}
      <div class="flex items-center space-x-2">
        <Switch />
        <Label for="airplane-mode">Airplane Mode</Label>
      </div>
    {:else if componentName === 'label'}
      <div class="flex items-center space-x-2">
        <Label for="email">Your email address</Label>
      </div>
    {:else if componentName === 'radio-group'}
      <RadioGroup.Root value="comfortable">
        <div class="flex items-center space-x-2">
          <RadioGroup.Item value="default" id="r1" />
          <Label for="r1">Default</Label>
        </div>
        <div class="flex items-center space-x-2">
          <RadioGroup.Item value="comfortable" id="r2" />
          <Label for="r2">Comfortable</Label>
        </div>
        <div class="flex items-center space-x-2">
          <RadioGroup.Item value="compact" id="r3" />
          <Label for="r3">Compact</Label>
        </div>
      </RadioGroup.Root>
    {:else if componentName === 'select'}
      <Select.Root type="single">
        <Select.Trigger class="w-[180px]">
          <Select.Value placeholder="Theme" />
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="light" label="Light">Light</Select.Item>
          <Select.Item value="dark" label="Dark">Dark</Select.Item>
          <Select.Item value="system" label="System">System</Select.Item>
        </Select.Content>
      </Select.Root>
    {:else}
      <div class="text-center text-muted-foreground p-10">
        <h3 class="text-lg font-medium">Preview not available yet</h3>
        <p class="mt-2">
          Preview for <b>{componentName}</b> is coming soon.
        </p>
        <p class="text-sm mt-2">
          Currently only the button component has a preview implemented.
        </p>
      </div>
    {/if}
  </div>

  <div class="mt-8 pt-8 border-t">
    <h2 class="text-xl font-semibold mb-4">Installation</h2>
    <div class="bg-muted p-4 rounded-lg overflow-x-auto">
       <pre class="font-mono text-sm"><code>import &lbrace; {componentName?.split('-').map(part => part.charAt(0).toUpperCase() + part.slice(1)).join('') || 'Component'} &rbrace; from '$lib/components/ui/{componentName || 'component'}';</code></pre>
    </div>
  </div>
</div>
