import Root from './input.svelte';

type Props = {
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search';
  class?: string;
  value?: string;
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  required?: boolean;
  name?: string;
  id?: string;
  'aria-label'?: string;
  'aria-describedby'?: string;
};

export {
  Root,
  type Props,
  //
  Root as Input,
  type Props as InputProps
};
