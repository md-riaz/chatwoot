import Root from './switch.svelte';

type Props = {
  class?: string;
  checked?: boolean;
  disabled?: boolean;
  required?: boolean;
  name?: string;
  value?: string;
};

export {
  Root,
  type Props,
  //
  Root as Switch,
  type Props as SwitchProps
};
