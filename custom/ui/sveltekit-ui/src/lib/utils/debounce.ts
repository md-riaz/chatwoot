// Utility for debouncing function calls
export function debounce<T extends (...args: any[]) => any>(
	func: T,
	wait: number
): (...args: Parameters<T>) => void {
	let timeout: ReturnType<typeof setTimeout> | null = null;

	return function executedFunction(...args: Parameters<T>) {
		const later = () => {
			timeout = null;
			func(...args);
		};

		if (timeout !== null) {
			clearTimeout(timeout);
		}
		timeout = setTimeout(later, wait);
	};
}

// Utility for request cancellation
export function createAbortController() {
	const controller = new AbortController();
	return {
		signal: controller.signal,
		abort: () => controller.abort()
	};
}
