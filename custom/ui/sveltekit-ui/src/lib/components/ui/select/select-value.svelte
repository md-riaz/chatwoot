<script lang="ts">
  import { Select as SelectPrimitive } from 'bits-ui';

  type Props = {
    placeholder?: string;
  };

  let { placeholder = 'Select an option', ...restProps }: Props = $props();
</script>

<SelectPrimitive.Value {placeholder} {...restProps} />
