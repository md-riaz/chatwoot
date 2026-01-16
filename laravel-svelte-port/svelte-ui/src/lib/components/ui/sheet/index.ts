import { Dialog as SheetPrimitive } from 'bits-ui';
import Sheet from './sheet.svelte';
import ContentWrapper from './sheet-content.svelte';

const Root = SheetPrimitive.Root;
const Trigger = SheetPrimitive.Trigger;
const Close = SheetPrimitive.Close;
const Portal = SheetPrimitive.Portal;
const Overlay = SheetPrimitive.Overlay;
const Content = ContentWrapper;
const Title = SheetPrimitive.Title;
const Description = SheetPrimitive.Description;

export {
  Root,
  Trigger,
  Close,
  Portal,
  Overlay,
  Content,
  Title,
  Description,
  //
  Root as Sheet,
  Trigger as SheetTrigger,
  Close as SheetClose,
  Portal as SheetPortal,
  Overlay as SheetOverlay,
  Content as SheetContent,
  Title as SheetTitle,
  Description as SheetDescription
};
