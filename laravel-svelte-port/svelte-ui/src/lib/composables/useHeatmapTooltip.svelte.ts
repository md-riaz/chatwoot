/**
 * Heatmap Tooltip Composable
 * Manages tooltip visibility and positioning for heatmap cells
 * Matches Vue useHeatmapTooltip.js functionality
 */

export function useHeatmapTooltip() {
  let visible = $state(false);
  let x = $state(0);
  let y = $state(0);
  let value = $state(0);

  function show(event: MouseEvent, cellValue: number) {
    visible = true;
    x = event.clientX;
    y = event.clientY;
    value = cellValue;
  }

  function hide() {
    visible = false;
  }

  function updatePosition(event: MouseEvent) {
    if (visible) {
      x = event.clientX;
      y = event.clientY;
    }
  }

  return {
    visible: () => visible,
    x: () => x,
    y: () => y,
    value: () => value,
    show,
    hide,
    updatePosition
  };
}