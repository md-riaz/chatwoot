import { render as testingLibraryRender, type RenderResult } from '@testing-library/svelte/svelte5';
import type { ComponentProps, SvelteComponent } from 'svelte';

/**
 * Enhanced render function with common setup
 */
export function render<T extends SvelteComponent>(
  component: new (...args: any[]) => T,
  options?: {
    props?: ComponentProps<T>;
    context?: Map<any, any>;
  }
): RenderResult<T> {
  const { props = {}, context } = options || {};
  
  return testingLibraryRender(component, {
    props,
    context
  });
}

/**
 * Wait for async updates
 */
export async function waitForAsync() {
  await new Promise(resolve => setTimeout(resolve, 0));
}
