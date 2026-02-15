<script lang="ts">
  import { Input } from '$lib/components/ui/input';

  interface Props {
    id?: string;
    value?: string;
    class?: string;
    textInputClass?: string;
    colorInputClass?: string;
    placeholder?: string;
  }

  let {
    id,
    value = $bindable('#1f93ff'),
    class: className = '',
    textInputClass = '',
    colorInputClass = '',
    placeholder = '#1f93ff',
  }: Props = $props();
</script>

<div class={`flex gap-2 ${className}`}>
  <Input
    {id}
    type={'color' as any}
    bind:value
    class={`h-10 w-20 ${colorInputClass}`}
  />
  <Input
    type="text"
    bind:value
    {placeholder}
    class={`flex-1 ${textInputClass}`}
  />
</div>
