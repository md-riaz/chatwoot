import Root from './form.svelte';
import Field from './form-field.svelte';
import Label from './form-label.svelte';
import Description from './form-description.svelte';
import Message from './form-message.svelte';

import type { FormProps } from './form.svelte';
import type { FormFieldProps } from './form-field.svelte';
import type { FormLabelProps } from './form-label.svelte';
import type { FormDescriptionProps } from './form-description.svelte';
import type { FormMessageProps } from './form-message.svelte';

export {
  Root,
  Field,
  Label,
  Description,
  Message,
  type FormProps,
  type FormFieldProps,
  type FormLabelProps,
  type FormDescriptionProps,
  type FormMessageProps,
  //
  Root as Form,
  Field as FormField,
  Label as FormLabel,
  Description as FormDescription,
  Message as FormMessage
};
