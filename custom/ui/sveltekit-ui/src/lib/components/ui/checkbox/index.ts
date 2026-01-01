import Root from './checkbox.svelte';

type Props = {
  class?: string;
  checked?: boolean | 'indeterminate';
  disabled?: boolean;
  required?: boolean;
  name?: string;
  value?: string;
};

export {
  Root,
  type Props,
  //
  Root as Checkbox,
  type Props as CheckboxProps
};
