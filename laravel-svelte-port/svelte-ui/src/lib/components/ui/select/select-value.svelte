<script lang="ts">
  type Props = {
    placeholder?: string;
  };

  let { placeholder = 'Select an option', ...restProps }: Props = $props();
</script>

<span {...restProps}>{placeholder}</span>
