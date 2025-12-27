import Root from './textarea.svelte';

type Props = {
  class?: string;
  value?: string;
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  required?: boolean;
  rows?: number;
  name?: string;
  id?: string;
};

export {
  Root,
  type Props,
  //
  Root as Textarea,
  type Props as TextareaProps
};
