<script lang="ts">
  import { Button } from '$lib/components/ui/button';
</script>

<div class="container mx-auto py-10">
  <h1 class="text-4xl font-bold mb-8">Chatwoot Svelte UI</h1>
  <p class="text-muted-foreground mb-6">
    A modern UI component library built with SvelteKit, shadcn-svelte, and Tailwind CSS.
  </p>
  
  <div class="flex gap-4">
    <Button>Get Started</Button>
    <Button variant="outline">View Components</Button>
  </div>
  
  <div class="mt-10">
    <h2 class="text-2xl font-semibold mb-4">Available Components</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Button</h3>
        <p class="text-sm text-muted-foreground">9 variants, 4 sizes</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Input</h3>
        <p class="text-sm text-muted-foreground">Text, email, password</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Badge</h3>
        <p class="text-sm text-muted-foreground">7 variants</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Avatar</h3>
        <p class="text-sm text-muted-foreground">4 sizes with fallback</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Card</h3>
        <p class="text-sm text-muted-foreground">Header, content, footer</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Checkbox</h3>
        <p class="text-sm text-muted-foreground">With indeterminate</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Switch</h3>
        <p class="text-sm text-muted-foreground">Toggle control</p>
      </div>
      <div class="p-4 border rounded-lg">
        <h3 class="font-medium">Spinner</h3>
        <p class="text-sm text-muted-foreground">4 sizes</p>
      </div>
    </div>
  </div>
  
  <div class="mt-10">
    <h2 class="text-2xl font-semibold mb-4">Quick Start</h2>
    <pre class="bg-muted p-4 rounded-lg text-sm overflow-x-auto"><code># Run Histoire to view component stories
pnpm story:dev

# Run development server
pnpm dev</code></pre>
  </div>
</div>
