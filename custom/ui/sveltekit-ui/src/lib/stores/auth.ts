import { writable } from 'svelte/store';
import { browser } from '$app/environment';

export interface User {
	id: number;
	name: string;
	email: string;
	role?: string;
}

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
				const userStr = localStorage.getItem('user');
				if (token && userStr) {
					try {
						const user = JSON.parse(userStr);
						set({ isAuthenticated: true, user, token, loading: false });
					} catch {
						localStorage.removeItem('auth_token');
						localStorage.removeItem('user');
						set({ ...initialState, loading: false });
					}
				} else {
					set({ ...initialState, loading: false });
				}
			}
		},
		login: (token: string, user: User) => {
			if (browser) {
				localStorage.setItem('auth_token', token);
				localStorage.setItem('user', JSON.stringify(user));
			}
			set({ isAuthenticated: true, user, token, loading: false });
		},
		logout: () => {
			if (browser) {
				localStorage.removeItem('auth_token');
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
