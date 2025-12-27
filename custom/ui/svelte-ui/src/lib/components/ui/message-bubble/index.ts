import { type VariantProps, tv } from 'tailwind-variants';
import Root from './message-bubble.svelte';
import Content from './message-bubble-content.svelte';
import Timestamp from './message-bubble-timestamp.svelte';
import Status from './message-bubble-status.svelte';
import Avatar from './message-bubble-avatar.svelte';

const messageBubbleVariants = tv({
  base: 'max-w-[80%] rounded-lg px-4 py-2',
  variants: {
    variant: {
      incoming: 'bg-muted text-foreground rounded-bl-none',
      outgoing: 'bg-primary text-primary-foreground rounded-br-none ml-auto',
      private: 'bg-warning/20 text-warning-foreground border border-warning/30 rounded-bl-none',
      bot: 'bg-info/20 text-info-foreground border border-info/30 rounded-bl-none'
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
