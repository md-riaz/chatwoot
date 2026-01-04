import { writable } from 'svelte/store';
import { browser } from '$app/environment';
import type { User } from '$lib/types';

export interface AuthState {
	isAuthenticated: boolean;
	user: User | null;
	token: string | null;
	loading: boolean;
}

const initialState: AuthState = {
	isAuthenticated: false,
	user: null,
	token: null,
	loading: true
};

function createAuthStore() {
	const { subscribe, set, update } = writable<AuthState>(initialState);

	return {
		subscribe,
		init: () => {
			if (browser) {
				const token = localStorage.getItem('auth_token');
				const client = localStorage.getItem('auth_client');
				const uid = localStorage.getItem('auth_uid');
				const userStr = localStorage.getItem('user');
				
				// All three tokens are required for valid session
				if (token && client && uid && userStr) {
					try {
						const user = JSON.parse(userStr);
						set({ isAuthenticated: true, user, token, loading: false });
					} catch {
						// Invalid user data, clear everything
						localStorage.removeItem('auth_token');
						localStorage.removeItem('auth_client');
						localStorage.removeItem('auth_uid');
						localStorage.removeItem('user');
						set({ ...initialState, loading: false });
					}
				} else {
					// Incomplete auth state, clear everything to ensure consistency
					if (token || client || uid || userStr) {
						localStorage.removeItem('auth_token');
						localStorage.removeItem('auth_client');
						localStorage.removeItem('auth_uid');
						localStorage.removeItem('user');
					}
					set({ ...initialState, loading: false });
				}
			}
		},
		login: (token: string, user: User, client?: string, uid?: string) => {
			if (browser) {
				localStorage.setItem('auth_token', token);
				if (client) localStorage.setItem('auth_client', client);
				if (uid) localStorage.setItem('auth_uid', uid);
				localStorage.setItem('user', JSON.stringify(user));
			}
			set({ isAuthenticated: true, user, token, loading: false });
		},
		logout: () => {
			if (browser) {
				localStorage.removeItem('auth_token');
				localStorage.removeItem('auth_client');
				localStorage.removeItem('auth_uid');
				localStorage.removeItem('user');
			}
			set({ isAuthenticated: false, user: null, token: null, loading: false });
		},
		updateUser: (user: User) => {
			if (browser) {
				localStorage.setItem('user', JSON.stringify(user));
			}
			update((state) => ({ ...state, user }));
		}
	};
}

export const authStore = createAuthStore();
