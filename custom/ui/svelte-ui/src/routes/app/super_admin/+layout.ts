import { redirect } from '@sveltejs/kit';
import { browser } from '$app/environment';
import type { LayoutLoad } from './$types';
import { authApi } from '$lib/api/superAdmin';

export const load: LayoutLoad = async () => {
	if (browser) {
		const token = localStorage.getItem('auth_token');
		if (!token) {
			throw redirect(307, '/login');
		}

		// Verify token validity and check if user is super admin
		try {
			const user = await authApi.getCurrentUser();
			
			// Check if user has super_admin role
			if (!user.roles?.includes('super_admin')) {
				// User is not a super admin, redirect to login with error
				throw redirect(307, '/login?error=not_authorized');
			}
			
			// Token is valid and user is super admin, proceed
		} catch (error) {
			// Token is invalid or expired
			localStorage.removeItem('auth_token');
			localStorage.removeItem('user');
			throw redirect(307, '/login');
		}
	}
	
	return {};
};
