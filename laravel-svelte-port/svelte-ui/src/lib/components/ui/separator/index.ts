import Root from './separator.svelte';
import type { HTMLAttributes } from 'svelte/elements';

type Props = HTMLAttributes<HTMLDivElement> & {
  class?: string;
  orientation?: 'horizontal' | 'vertical';
  decorative?: boolean;
  ref?: HTMLDivElement | null;
};

export {
  Root,
  type Props,
  //
  Root as Separator,
  type Props as SeparatorProps
};
