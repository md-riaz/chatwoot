import { redirect } from '@sveltejs/kit';
import type { PageLoad } from './$types';

export const load: PageLoad = async ({ url }) => {
	// Preserve query parameters when redirecting
	const queryString = url.search;
	throw redirect(307, `/app/signup${queryString}`);
};
