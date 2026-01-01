import { redirect } from '@sveltejs/kit';
import { browser } from '$app/environment';
import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async () => {
	if (browser) {
		const token = localStorage.getItem('auth_token');
		if (!token) {
			throw redirect(307, '/login');
		}
	}
	
	return {};
};
