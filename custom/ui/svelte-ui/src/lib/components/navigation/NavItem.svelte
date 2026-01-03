<script lang="ts">
  /**
   * NavItem - Individual navigation item
   */
  
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { navigate } from '$lib/routing/navigation';
  import type { NavItem as NavItemType } from './types';
  
  interface Props {
    item: NavItemType;
    isActive?: boolean;
    onclick?: () => void;
  }
  
  let { item, isActive = false, onclick }: Props = $props();
  
  function handleClick() {
    if (onclick) {
      onclick();
    } else {
      navigate(item.href);
    }
  }
</script>

<Button
  variant={isActive ? 'secondary' : 'ghost'}
  class="w-full justify-start gap-3 {isActive ? 'font-medium' : ''}"
  onclick={handleClick}
>
  {#if item.icon}
    <span class="text-lg">{item.icon}</span>
  {/if}
  <span class="flex-1 text-left">{item.label}</span>
  {#if item.badge && item.badge > 0}
    <Badge variant="default" class="ml-auto">
      {item.badge > 99 ? '99+' : item.badge}
    </Badge>
  {/if}
</Button>
