import Root from './textarea.svelte';
import type { HTMLTextareaAttributes } from 'svelte/elements';

type Props = HTMLTextareaAttributes & {
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
