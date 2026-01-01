<script lang="ts">
	import { Input } from '$lib/components/ui/input';
	import { Search } from 'lucide-svelte';
	import { debounce } from '$lib/utils/debounce';
	
	interface SearchInputProps {
		value?: string;
		placeholder?: string;
		onSearch: (query: string) => void;
		debounceMs?: number;
	}
	
	let {
		value = $bindable(''),
		placeholder = 'Search...',
		onSearch,
		debounceMs = 300
	}: SearchInputProps = $props();
	
	// Create debounced search function
	const debouncedSearch = debounce((query: string) => {
		onSearch(query);
	}, debounceMs);
	
	function handleInput(e: Event) {
		const target = e.target as HTMLInputElement;
		value = target.value;
		debouncedSearch(value);
	}
</script>

<div class="relative">
	<Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4" style="color: rgb(var(--slate-10));" aria-hidden="true" />
	<Input
		type="search"
		{placeholder}
		{value}
		oninput={handleInput}
		class="pl-10"
		aria-label={placeholder}
	/>
</div>
