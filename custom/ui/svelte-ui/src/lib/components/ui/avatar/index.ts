import { type VariantProps, tv } from 'tailwind-variants';
import Root from './avatar.svelte';
import Image from './avatar-image.svelte';
import Fallback from './avatar-fallback.svelte';

const avatarVariants = tv({
  base: 'relative flex shrink-0 overflow-hidden rounded-full',
  variants: {
    size: {
      default: 'h-10 w-10',
      sm: 'h-8 w-8',
      lg: 'h-12 w-12',
      xl: 'h-16 w-16'
    }
  },
  defaultVariants: {
    size: 'default'
  }
});

type Size = VariantProps<typeof avatarVariants>['size'];

type Props = {
  class?: string;
  size?: Size;
};

export {
  Root,
  Image,
  Fallback,
  type Props,
  avatarVariants,
  //
  Root as Avatar,
  Image as AvatarImage,
  Fallback as AvatarFallback,
  type Props as AvatarProps
};
