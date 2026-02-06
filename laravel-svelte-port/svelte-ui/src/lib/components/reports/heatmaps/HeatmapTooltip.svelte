<script lang="ts">
  interface Props {
    visible: boolean;
    x: number;
    y: number;
    value: number;
  }
  
  let { visible, x, y, value }: Props = $props();
  
  // Position tooltip to avoid going off screen
  const tooltipStyle = $derived.by(() => {
    if (!visible) return 'display: none;';
    
    const offset = 10;
    let left = x + offset;
    let top = y - 40; // Position above cursor
    
    // Adjust if tooltip would go off right edge
    if (left > window.innerWidth - 120) {
      left = x - 120 - offset;
    }
    
    // Adjust if tooltip would go off top edge
    if (top < 0) {
      top = y + offset;
    }
    
    return `
      position: fixed;
      left: ${left}px;
      top: ${top}px;
      z-index: 1000;
      pointer-events: none;
    `;
  });
</script>

{#if visible}
  <div 
    class="heatmap-tooltip bg-slate-900 dark:bg-slate-800 text-white text-xs px-2 py-1 rounded shadow-lg border border-slate-700"
    style={tooltipStyle}
  >
    <div class="font-medium">
      {value} {value === 1 ? 'conversation' : 'conversations'}
    </div>
  </div>
{/if}

<style>
  .heatmap-tooltip {
    white-space: nowrap;
    max-width: 200px;
  }
</style>