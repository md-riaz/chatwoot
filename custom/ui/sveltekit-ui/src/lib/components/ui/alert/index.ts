import { type VariantProps, tv } from 'tailwind-variants';
import Root from './alert.svelte';
import Description from './alert-description.svelte';
import Title from './alert-title.svelte';

const alertVariants = tv({
  base: 'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
  variants: {
    variant: {
      default: 'bg-background text-foreground',
      destructive:
        'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
      success:
        'border-success/50 text-success dark:border-success [&>svg]:text-success',
      warning:
        'border-warning/50 text-warning dark:border-warning [&>svg]:text-warning',
      info:
        'border-info/50 text-info dark:border-info [&>svg]:text-info'
    }
  },
  defaultVariants: {
    variant: 'default'
  }
});

type Variant = VariantProps<typeof alertVariants>['variant'];

type Props = {
  class?: string;
  variant?: Variant;
};

export {
  Root,
  Description,
  Title,
  alertVariants,
  type Props,
  //
  Root as Alert,
  Description as AlertDescription,
  Title as AlertTitle
};
