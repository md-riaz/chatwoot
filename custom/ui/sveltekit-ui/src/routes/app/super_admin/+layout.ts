import { redirect } from '@sveltejs/kit';
import { browser } from '$app/environment';
import type { LayoutLoad } from './$types';
import { authApi } from '$lib/api/client';

export const load: LayoutLoad = async () => {
	if (browser) {
		const token = localStorage.getItem('auth_token');
		if (!token) {
			throw redirect(307, '/login');
		}

		// Verify token validity by fetching current user
		try {
			await authApi.getCurrentUser();
			// Token is valid, proceed
		} catch (error) {
			// Token is invalid or expired
			localStorage.removeItem('auth_token');
			localStorage.removeItem('user');
			throw redirect(307, '/login');
		}
	}
	
	return {};
};
