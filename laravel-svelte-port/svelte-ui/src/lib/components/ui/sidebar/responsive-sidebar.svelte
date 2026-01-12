<script lang="ts">
  import { cn } from '$lib/utils';
  import { Sheet } from '$lib/components/ui/sheet';
  import { Menu } from 'lucide-svelte';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
    mobileBreakpoint?: string;
  };

  let {
    class: className,
    children,
    mobileBreakpoint = 'lg',
    ...restProps
  }: Props = $props();

  // State for mobile sidebar
  let mobileOpen = $state(false);

  // Close mobile sidebar
  function closeMobileSidebar() {
    mobileOpen = false;
  }

  // Toggle mobile sidebar
  function toggleMobileSidebar() {
    mobileOpen = !mobileOpen;
  }

  // Breakpoint class mapping
  const breakpointHidden: Record<string, string> = {
    sm: 'sm:hidden',
    md: 'md:hidden',
    lg: 'lg:hidden',
    xl: 'xl:hidden',
    '2xl': '2xl:hidden',
  };

  const breakpointVisible: Record<string, string> = {
    sm: 'hidden sm:flex',
    md: 'hidden md:flex',
    lg: 'hidden lg:flex',
    xl: 'hidden xl:flex',
    '2xl': 'hidden 2xl:flex',
  };
</script>

<!-- Mobile Menu Button -->
<button
  type="button"
  onclick={toggleMobileSidebar}
  class={cn(
    'fixed top-4 left-4 z-40 p-2 rounded-md bg-background border border-border shadow-lg',
    breakpointHidden[mobileBreakpoint] || 'lg:hidden'
  )}
  aria-label="Toggle menu"
>
  <Menu class="h-6 w-6" />
</button>

<!-- Desktop Sidebar -->
<aside
  class={cn(
    'flex flex-col h-full bg-card border-r border-border',
    breakpointVisible[mobileBreakpoint] || 'hidden lg:flex',
    'w-64',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</aside>

<!-- Mobile Sidebar (Sheet) -->
<Sheet bind:open={mobileOpen} side="left" onClose={closeMobileSidebar}>
  <div class="flex flex-col h-[calc(100vh-3rem)] overflow-hidden">
    {#if children}
      {@render children()}
    {/if}
  </div>
</Sheet>
