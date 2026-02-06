<script lang="ts">
  import { fade, fly } from 'svelte/transition';
  import { cubicOut } from 'svelte/easing';
  
  interface Props {
    type?: 'fade' | 'fly' | 'slide';
    duration?: number;
    delay?: number;
    children?: any;
  }
  
  let { 
    type = 'fade', 
    duration = 200, 
    delay = 0,
    children 
  }: Props = $props();
  
  const transitions = {
    fade: { in: fade, out: fade },
    fly: { 
      in: (node: Element) => fly(node, { y: 10, duration, easing: cubicOut }),
      out: (node: Element) => fly(node, { y: -10, duration: duration / 2, easing: cubicOut })
    },
    slide: {
      in: (node: Element) => fly(node, { x: -20, duration, easing: cubicOut }),
      out: (node: Element) => fly(node, { x: 20, duration: duration / 2, easing: cubicOut })
    }
  };
</script>

{#if children}
  <div 
    in:transitions[type].in={{ duration, delay }}
    out:transitions[type].out={{ duration: duration / 2 }}
  >
    {@render children()}
  </div>
{/if}