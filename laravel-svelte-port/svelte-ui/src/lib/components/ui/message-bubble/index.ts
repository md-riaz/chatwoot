import { type VariantProps, tv } from 'tailwind-variants';
import Root from './message-bubble.svelte';
import Content from './message-bubble-content.svelte';
import Timestamp from './message-bubble-timestamp.svelte';
import Status from './message-bubble-status.svelte';
import Avatar from './message-bubble-avatar.svelte';

const messageBubbleVariants = tv({
  base: 'max-w-[85%] rounded-2xl px-4 py-2.5 shadow-sm text-sm transition-all',
  variants: {
    variant: {
      incoming: 'bg-white dark:bg-slate-800 text-foreground rounded-tl-sm border border-slate-100 dark:border-slate-700',
      outgoing: 'bg-primary text-primary-foreground rounded-tr-sm shadow-primary/20',
      private: 'bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 border border-amber-200 dark:border-amber-900/50 rounded-tl-sm shadow-amber-900/5',
      bot: 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-900/50 rounded-tl-sm shadow-indigo-900/5'
    }
  },
  defaultVariants: {
    variant: 'incoming'
  }
});

type Variant = VariantProps<typeof messageBubbleVariants>['variant'];

type Props = {
  class?: string;
  variant?: Variant;
};

export {
  Root,
  Content,
  Timestamp,
  Status,
  Avatar,
  messageBubbleVariants,
  type Props,
  type Variant,
  //
  Root as MessageBubble,
  Content as MessageBubbleContent,
  Timestamp as MessageBubbleTimestamp,
  Status as MessageBubbleStatus,
  Avatar as MessageBubbleAvatar
};
