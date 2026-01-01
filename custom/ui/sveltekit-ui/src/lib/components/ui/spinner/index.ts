import { type VariantProps, tv } from 'tailwind-variants';
import Root from './spinner.svelte';

const spinnerVariants = tv({
  base: 'animate-spin',
  variants: {
    size: {
      default: 'h-5 w-5',
      sm: 'h-4 w-4',
      lg: 'h-8 w-8',
      xl: 'h-12 w-12'
    }
  },
  defaultVariants: {
    size: 'default'
  }
});

type Size = VariantProps<typeof spinnerVariants>['size'];

type Props = {
  class?: string;
  size?: Size;
};

export {
  Root,
  type Props,
  spinnerVariants,
  //
  Root as Spinner,
  type Props as SpinnerProps
};
