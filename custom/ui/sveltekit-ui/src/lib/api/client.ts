import ky from 'ky';
import { browser } from '$app/environment';
import { goto } from '$app/navigation';
import type {
	User,
	Account,
	DashboardData,
	AgentBot,
	PlatformApp,
	AccessToken,
	InstallationConfig,
	AccountUser,
	AuditLog,
	Setting,
	PaginationParams,
	AuthResponse,
	OnboardingData
} from '$lib/types';

const API_BASE_URL = browser
	? import.meta.env.VITE_API_URL || 'http://localhost:3000'
	: 'http://localhost:3000';

// Create API client with default configuration
export const api = ky.create({
	prefixUrl: API_BASE_URL,
	timeout: 30000,
	hooks: {
		beforeRequest: [
			(request) => {
				// Add authentication headers if available (devise_token_auth format)
				if (browser) {
					const token = localStorage.getItem('auth_token');
					const client = localStorage.getItem('auth_client');
					const uid = localStorage.getItem('auth_uid');
					
					if (token && client && uid) {
						request.headers.set('access-token', token);
						request.headers.set('client', client);
						request.headers.set('uid', uid);
					}
				}
			}
		],
		afterResponse: [
			async (request, options, response) => {
				// Store auth headers from response (devise_token_auth)
				if (browser && response.ok) {
					const token = response.headers.get('access-token');
					const client = response.headers.get('client');
					const uid = response.headers.get('uid');
					
					if (token && client && uid) {
						localStorage.setItem('auth_token', token);
						localStorage.setItem('auth_client', client);
						localStorage.setItem('auth_uid', uid);
					}
				}
				
				// Handle 401 Unauthorized - redirect to login
				// Prevent redirect loop by checking current path
				if (response.status === 401 && browser) {
					const currentPath = window.location.pathname;
					// Only redirect if not already on login or onboarding pages
					if (currentPath !== '/login' && currentPath !== '/onboarding') {
						localStorage.removeItem('auth_token');
						localStorage.removeItem('auth_client');
						localStorage.removeItem('auth_uid');
						localStorage.removeItem('user');
						goto('/login');
					}
				}
				return response;
			}
		]
	}
});

// Super Admin API endpoints
export const superAdminApi = {
	// Dashboard
	getDashboard: () => api.get('super_admin/dashboard').json<DashboardData>(),
	getInstanceStatus: () => api.get('super_admin/instance_status').json<Record<string, unknown>>(),

	// Accounts
	getAccounts: (params?: PaginationParams) =>
		api.get('super_admin/accounts', { searchParams: params as Record<string, string> }).json<{ data: Account[] }>(),
	getAccount: (id: number) => api.get(`super_admin/accounts/${id}`).json<Account>(),
	createAccount: (data: Partial<Account>) => api.post('super_admin/accounts', { json: data }).json<Account>(),
	updateAccount: (id: number, data: Partial<Account>) =>
		api.put(`super_admin/accounts/${id}`, { json: data }).json<Account>(),
	deleteAccount: (id: number) => api.delete(`super_admin/accounts/${id}`).json<{ success: boolean }>(),

	// Users
	getUsers: (params?: PaginationParams) =>
		api.get('super_admin/users', { searchParams: params as Record<string, string> }).json<{ data: User[] }>(),
	getUser: (id: number) => api.get(`super_admin/users/${id}`).json<User>(),
	createUser: (data: Partial<User> & { password: string }) => api.post('super_admin/users', { json: data }).json<User>(),
	updateUser: (id: number, data: Partial<User>) =>
		api.put(`super_admin/users/${id}`, { json: data }).json<User>(),
	deleteUser: (id: number) => api.delete(`super_admin/users/${id}`).json<{ success: boolean }>(),
	uploadUserAvatar: (id: number, file: File) => {
		const formData = new FormData();
		formData.append('avatar', file);
		return api.post(`super_admin/users/${id}/avatar`, { body: formData }).json<User>();
	},
	deleteUserAvatar: (id: number) => api.delete(`super_admin/users/${id}/avatar`).json<User>(),
	confirmUserEmail: (id: number) => api.post(`super_admin/users/${id}/confirm`).json<User>(),
	lockUser: (id: number) => api.post(`super_admin/users/${id}/lock`).json<User>(),
	unlockUser: (id: number) => api.post(`super_admin/users/${id}/unlock`).json<User>(),

	// Settings
	getSettings: () => api.get('super_admin/settings').json<Setting[]>(),
	getSettingsGrouped: () => api.get('super_admin/settings/show').json<Record<string, Setting[]>>(),
	updateSettings: (data: Record<string, unknown>) => api.patch('super_admin/settings', { json: data }).json<{ success: boolean }>(),
	createSetting: (data: Partial<Setting>) => api.post('super_admin/settings', { json: data }).json<Setting>(),
	deleteSetting: (name: string) => api.delete(`super_admin/settings/${name}`).json<{ success: boolean }>(),

	// Agent Bots
	getAgentBots: (params?: PaginationParams) =>
		api.get('super_admin/agent_bots', { searchParams: params as Record<string, string> }).json<{ data: AgentBot[] }>(),
	getAgentBot: (id: number) => api.get(`super_admin/agent_bots/${id}`).json<AgentBot>(),
	createAgentBot: (data: Partial<AgentBot>) => api.post('super_admin/agent_bots', { json: data }).json<AgentBot>(),
	updateAgentBot: (id: number, data: Partial<AgentBot>) =>
		api.put(`super_admin/agent_bots/${id}`, { json: data }).json<AgentBot>(),
	deleteAgentBot: (id: number) => api.delete(`super_admin/agent_bots/${id}`).json<{ success: boolean }>(),

	// Platform Apps
	getPlatformApps: (params?: PaginationParams) =>
		api
			.get('super_admin/platform_apps', { searchParams: params as Record<string, string> })
			.json<{ data: PlatformApp[] }>(),
	getPlatformApp: (id: number) => api.get(`super_admin/platform_apps/${id}`).json<PlatformApp>(),
	createPlatformApp: (data: Partial<PlatformApp>) =>
		api.post('super_admin/platform_apps', { json: data }).json<PlatformApp>(),
	updatePlatformApp: (id: number, data: Partial<PlatformApp>) =>
		api.put(`super_admin/platform_apps/${id}`, { json: data }).json<PlatformApp>(),
	deletePlatformApp: (id: number) => api.delete(`super_admin/platform_apps/${id}`).json<{ success: boolean }>(),

	// Access Tokens
	getAccessTokens: (params?: PaginationParams) =>
		api
			.get('super_admin/access_tokens', { searchParams: params as Record<string, string> })
			.json<{ data: AccessToken[] }>(),
	createAccessToken: (data: { name: string }) =>
		api.post('super_admin/access_tokens', { json: data }).json<AccessToken>(),
	deleteAccessToken: (id: number) => api.delete(`super_admin/access_tokens/${id}`).json<{ success: boolean }>(),

	// Installation Configs
	getInstallationConfigs: (params?: PaginationParams) =>
		api
			.get('super_admin/installation_configs', { searchParams: params as Record<string, string> })
			.json<{ data: InstallationConfig[] }>(),
	getInstallationConfig: (id: number) => api.get(`super_admin/installation_configs/${id}`).json<InstallationConfig>(),
	updateInstallationConfig: (id: number, data: Partial<InstallationConfig>) =>
		api.patch(`super_admin/installation_configs/${id}`, { json: data }).json<InstallationConfig>(),

	// Account Users
	getAccountUsers: (params?: PaginationParams) =>
		api
			.get('super_admin/account_users', { searchParams: params as Record<string, string> })
			.json<{ data: AccountUser[] }>(),
	createAccountUser: (data: { user_id: number; account_id: number; role: string }) =>
		api.post('super_admin/account_users', { json: data }).json<AccountUser>(),
	updateAccountUser: (id: number, data: { role?: string }) =>
		api.put(`super_admin/account_users/${id}`, { json: data }).json<AccountUser>(),
	deleteAccountUser: (id: number) => api.delete(`super_admin/account_users/${id}`).json<{ success: boolean }>(),

	// Audit Logs
	getAuditLogs: (params?: PaginationParams) =>
		api.get('super_admin/audit_logs', { searchParams: params as Record<string, string> }).json<{ data: AuditLog[] }>(),

	// Cache
	clearCache: (type?: string) => {
		if (type) {
			return api.post(`super_admin/cache/clear/${type}`).json<{ success: boolean }>();
		}
		return api.post('super_admin/cache/clear').json<{ success: boolean }>();
	}
};

// Authentication API endpoints
export const authApi = {
	login: async (email: string, password: string) => {
		const response = await api.post('auth/sign_in', { json: { email, password } });
		const data = await response.json<AuthResponse>();
		// Extract token from headers (devise_token_auth sends it in access-token header)
		const token = response.headers.get('access-token') || '';
		const client = response.headers.get('client') || '';
		const uid = response.headers.get('uid') || '';
		return { token, client, uid, user: data.data };
	},
	logout: () => api.delete('auth/sign_out').json<{ success: boolean }>(),
	getCurrentUser: () => api.get('auth/validate_token').json<AuthResponse>().then(res => res.data)
};

// Onboarding API endpoints
export const onboardingApi = {
	checkOnboardingStatus: () => api.get('installation/onboarding/status').json<OnboardingData>(),
	completeOnboarding: async (data: { name: string; company: string; email: string; password: string }) => {
		const response = await api.post('installation/onboarding', { json: data });
		const responseData = await response.json<AuthResponse>();
		// Extract token from headers
		const token = response.headers.get('access-token') || '';
		const client = response.headers.get('client') || '';
		const uid = response.headers.get('uid') || '';
		return { token, client, uid, user: responseData.data };
	}
};

// Simplified API export for components
export const superAdminAPI = {
	dashboard: {
		get: () => superAdminApi.getDashboard(),
		status: () => superAdminApi.getInstanceStatus()
	},
	accounts: {
		list: (params?: PaginationParams) => superAdminApi.getAccounts(params),
		get: (id: string) => superAdminApi.getAccount(parseInt(id)),
		create: (data: Partial<Account>) => superAdminApi.createAccount(data),
		update: (id: string, data: Partial<Account>) => superAdminApi.updateAccount(parseInt(id), data),
		delete: (id: string) => superAdminApi.deleteAccount(parseInt(id))
	},
	users: {
		list: (params?: PaginationParams) => superAdminApi.getUsers(params),
		get: (id: string) => superAdminApi.getUser(parseInt(id)),
		create: (data: Partial<User> & { password: string }) => superAdminApi.createUser(data),
		update: (id: string, data: Partial<User>) => superAdminApi.updateUser(parseInt(id), data),
		delete: (id: string) => superAdminApi.deleteUser(parseInt(id)),
		uploadAvatar: (id: string, file: File) => superAdminApi.uploadUserAvatar(parseInt(id), file),
		deleteAvatar: (id: string) => superAdminApi.deleteUserAvatar(parseInt(id)),
		confirmEmail: (id: string) => superAdminApi.confirmUserEmail(parseInt(id)),
		lock: (id: string) => superAdminApi.lockUser(parseInt(id)),
		unlock: (id: string) => superAdminApi.unlockUser(parseInt(id))
	},
	settings: {
		get: () => superAdminApi.getSettings(),
		getGrouped: () => superAdminApi.getSettingsGrouped(),
		update: (data: Record<string, unknown>) => superAdminApi.updateSettings(data),
		create: (data: Partial<Setting>) => superAdminApi.createSetting(data),
		delete: (name: string) => superAdminApi.deleteSetting(name)
	}
};
