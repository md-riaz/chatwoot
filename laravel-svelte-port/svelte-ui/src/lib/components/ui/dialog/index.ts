import { Dialog as DialogPrimitive } from 'bits-ui';
import Root from './dialog.svelte';
import Content from './dialog-content.svelte';
import Description from './dialog-description.svelte';
import Footer from './dialog-footer.svelte';
import Header from './dialog-header.svelte';
import Title from './dialog-title.svelte';
import Overlay from './dialog-overlay.svelte';
import Portal from './dialog-portal.svelte';
import Close from './dialog-close.svelte';

const Trigger = DialogPrimitive.Trigger;

export {
  Root,
  Content,
  Description,
  Footer,
  Header,
  Title,
  Trigger,
  Overlay,
  Portal,
  Close,
  //
  Root as Dialog,
  Content as DialogContent,
  Description as DialogDescription,
  Footer as DialogFooter,
  Header as DialogHeader,
  Title as DialogTitle,
  Trigger as DialogTrigger,
  Overlay as DialogOverlay,
  Portal as DialogPortal,
  Close as DialogClose
};
