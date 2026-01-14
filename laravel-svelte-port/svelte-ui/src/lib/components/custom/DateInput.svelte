<script lang="ts">
	import { DatePicker } from '$lib/components/ui/date-picker';
	import { parseDate, type DateValue } from '@internationalized/date';
	import { cn } from '$lib/utils';

	let {
		value = $bindable(''),
		placeholder = 'Pick a date',
		class: className,
		disabled = false,
		...restProps
	} = $props<{
		value?: string;
		placeholder?: string;
		class?: string;
		disabled?: boolean;
	}>();

	// Convert between string and DateValue
	let dateValue = $state<DateValue | undefined>(
		value ? parseDate(value) : undefined
	);

	// Sync DateValue back to string when it changes
	$effect(() => {
		if (dateValue) {
			value = dateValue.toString();
		} else {
			value = '';
		}
	});

	// Sync string value to DateValue when external value changes
	$effect(() => {
		if (value && (!dateValue || dateValue.toString() !== value)) {
			try {
				dateValue = parseDate(value);
			} catch (e) {
				// Invalid date format, keep dateValue as is
				console.warn('Invalid date format:', value);
			}
		} else if (!value && dateValue) {
			dateValue = undefined;
		}
	});
</script>

<DatePicker
	bind:value={dateValue}
	{placeholder}
	{disabled}
	class={cn('w-40', className)}
	{...restProps}
/>
