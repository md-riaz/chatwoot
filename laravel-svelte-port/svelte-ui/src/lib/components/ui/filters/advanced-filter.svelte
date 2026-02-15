<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import ContactAdvancedFilter from '$lib/components/ui/contact-management/advanced-filter.svelte';
  import type { FilterCondition } from '$lib/constants/filter-types';

  interface LabelOption {
    id: string;
    name: string;
    title?: string;
  }

  interface Props {
    open?: boolean;
    filters?: FilterCondition[];
    labels?: LabelOption[];
  }

  let {
    open = $bindable(false),
    filters = $bindable<FilterCondition[]>([]),
    labels = [],
  }: Props = $props();

  const dispatch = createEventDispatcher<{
    apply: FilterCondition[];
    clear: void;
  }>();
</script>

<ContactAdvancedFilter
  bind:open
  bind:filters
  {labels}
  on:apply={event => dispatch('apply', event.detail)}
  on:clear={() => dispatch('clear')}
/>
