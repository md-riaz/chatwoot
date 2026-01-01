<script lang="ts">
	import {
		Dialog,
		DialogContent,
		DialogDescription,
		DialogFooter,
		DialogHeader,
		DialogTitle
	} from '$lib/components/ui/dialog';
	import { Button } from '$lib/components/ui/button';

	interface ConfirmDialogProps {
		open?: boolean;
		title?: string;
		description?: string;
		confirmText?: string;
		cancelText?: string;
		variant?: 'default' | 'destructive';
		onConfirm: () => void | Promise<void>;
		onCancel?: () => void;
	}

	let {
		open = $bindable(false),
		title = 'Confirm Action',
		description = 'Are you sure you want to proceed?',
		confirmText = 'Confirm',
		cancelText = 'Cancel',
		variant = 'default',
		onConfirm,
		onCancel
	}: ConfirmDialogProps = $props();

	let isLoading = $state(false);

	async function handleConfirm() {
		isLoading = true;
		try {
			await onConfirm();
			open = false;
		} catch (error) {
			console.error('Confirm action failed:', error);
		} finally {
			isLoading = false;
		}
	}

	function handleCancel() {
		if (onCancel) {
			onCancel();
		}
		open = false;
	}
</script>

<Dialog bind:open>
	<DialogContent class="sm:max-w-md">
		<DialogHeader>
			<DialogTitle>{title}</DialogTitle>
			<DialogDescription>{description}</DialogDescription>
		</DialogHeader>
		<DialogFooter class="gap-2 sm:gap-0">
			<Button variant="outline" onclick={handleCancel} disabled={isLoading}>
				{cancelText}
			</Button>
			<Button
				{variant}
				onclick={handleConfirm}
				disabled={isLoading}
				class={isLoading ? 'opacity-50 cursor-not-allowed' : ''}
			>
				{#if isLoading}
					<span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin mr-2"></span>
				{/if}
				{confirmText}
			</Button>
		</DialogFooter>
	</DialogContent>
</Dialog>
