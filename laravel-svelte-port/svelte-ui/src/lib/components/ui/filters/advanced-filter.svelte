<script lang="ts">
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
    onapply?: (filters: FilterCondition[]) => void;
    onclear?: () => void;
  }

  let {
    open = $bindable(false),
    filters = $bindable<FilterCondition[]>([]),
    labels = [],
    onapply = (_filters: FilterCondition[]) => {},
    onclear = () => {},
  }: Props = $props();
</script>

<ContactAdvancedFilter bind:open bind:filters {labels} {onapply} {onclear} />
