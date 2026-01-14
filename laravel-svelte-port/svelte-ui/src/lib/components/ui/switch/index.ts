import Root from './switch.svelte';
import type { HTMLAttributes } from 'svelte/elements';

type Props = HTMLAttributes<HTMLButtonElement> & {
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
