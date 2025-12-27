import Root from './separator.svelte';

type Props = {
  class?: string;
  orientation?: 'horizontal' | 'vertical';
  decorative?: boolean;
};

export {
  Root,
  type Props,
  //
  Root as Separator,
  type Props as SeparatorProps
};
