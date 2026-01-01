import Root from './progress.svelte';

type Props = {
  class?: string;
  value?: number;
  max?: number;
};

export {
  Root,
  type Props,
  //
  Root as Progress,
  type Props as ProgressProps
};
