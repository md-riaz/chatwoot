import Root from './label.svelte';

type Props = {
  class?: string;
  for?: string;
};

export {
  Root,
  type Props,
  //
  Root as Label,
  type Props as LabelProps
};
