import { Select as SelectPrimitive } from 'bits-ui';
import Content from './select-content.svelte';
import Item from './select-item.svelte';
import Trigger from './select-trigger.svelte';
import Value from './select-value.svelte';
import Separator from './select-separator.svelte';
import Label from './select-label.svelte';
import Group from './select-group.svelte';

const Root = SelectPrimitive.Root;

export {
  Root,
  Content,
  Item,
  Trigger,
  Value,
  Separator,
  Label,
  Group,
  //
  Root as Select,
  Content as SelectContent,
  Item as SelectItem,
  Trigger as SelectTrigger,
  Value as SelectValue,
  Separator as SelectSeparator,
  Label as SelectLabel,
  Group as SelectGroup
};
