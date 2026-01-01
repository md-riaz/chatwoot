import ky from 'ky';
import { browser } from '$app/environment';
import { goto } from '$app/navigation';

const API_BASE_URL = browser
	? import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'
	: 'http://localhost:8000/api/v1';

// Create API client with default configuration
export const api = ky.create({
	prefixUrl: API_BASE_URL,
	timeout: 30000,
	hooks: {
		beforeRequest: [
			(request) => {
				// Add authentication token if available
				if (browser) {
					const token = localStorage.getItem('auth_token');
					if (token) {
						request.headers.set('Authorization', `Bearer ${token}`);
					}
				}
			}
		],
		afterResponse: [
			async (request, options, response) => {
				// Handle 401 Unauthorized - redirect to login
				if (response.status === 401 && browser) {
					localStorage.removeItem('auth_token');
					localStorage.removeItem('user');
					goto('/login');
				}
				return response;
			}
		]
	}
});

// Super Admin API endpoints
export const superAdminApi = {
	// Dashboard
	getDashboard: () => api.get('super_admin/dashboard').json(),
	getInstanceStatus: () => api.get('super_admin/instance_status').json(),

	// Accounts
	getAccounts: (params?: Record<string, unknown>) =>
		api.get('super_admin/accounts', { searchParams: params }).json(),
	getAccount: (id: number) => api.get(`super_admin/accounts/${id}`).json(),
	createAccount: (data: unknown) => api.post('super_admin/accounts', { json: data }).json(),
	updateAccount: (id: number, data: unknown) =>
		api.put(`super_admin/accounts/${id}`, { json: data }).json(),
	deleteAccount: (id: number) => api.delete(`super_admin/accounts/${id}`).json(),

	// Users
	getUsers: (params?: Record<string, unknown>) =>
		api.get('super_admin/users', { searchParams: params }).json(),
	getUser: (id: number) => api.get(`super_admin/users/${id}`).json(),
	createUser: (data: unknown) => api.post('super_admin/users', { json: data }).json(),
	updateUser: (id: number, data: unknown) =>
		api.put(`super_admin/users/${id}`, { json: data }).json(),
	deleteUser: (id: number) => api.delete(`super_admin/users/${id}`).json(),
	deleteUserAvatar: (id: number) => api.delete(`super_admin/users/${id}/avatar`).json(),

	// Settings
	getSettings: () => api.get('super_admin/settings').json(),
	getSettingsGrouped: () => api.get('super_admin/settings/show').json(),
	updateSettings: (data: unknown) => api.patch('super_admin/settings', { json: data }).json(),
	createSetting: (data: unknown) => api.post('super_admin/settings', { json: data }).json(),
	deleteSetting: (name: string) => api.delete(`super_admin/settings/${name}`).json(),

	// Agent Bots
	getAgentBots: (params?: Record<string, unknown>) =>
		api.get('super_admin/agent_bots', { searchParams: params }).json(),
	getAgentBot: (id: number) => api.get(`super_admin/agent_bots/${id}`).json(),
	createAgentBot: (data: unknown) => api.post('super_admin/agent_bots', { json: data }).json(),
	updateAgentBot: (id: number, data: unknown) =>
		api.put(`super_admin/agent_bots/${id}`, { json: data }).json(),
	deleteAgentBot: (id: number) => api.delete(`super_admin/agent_bots/${id}`).json(),

	// Platform Apps
	getPlatformApps: (params?: Record<string, unknown>) =>
		api.get('super_admin/platform_apps', { searchParams: params }).json(),
	getPlatformApp: (id: number) => api.get(`super_admin/platform_apps/${id}`).json(),
	createPlatformApp: (data: unknown) =>
		api.post('super_admin/platform_apps', { json: data }).json(),
	updatePlatformApp: (id: number, data: unknown) =>
		api.put(`super_admin/platform_apps/${id}`, { json: data }).json(),
	deletePlatformApp: (id: number) => api.delete(`super_admin/platform_apps/${id}`).json(),

	// Access Tokens
	getAccessTokens: (params?: Record<string, unknown>) =>
		api.get('super_admin/access_tokens', { searchParams: params }).json(),
	createAccessToken: (data: unknown) =>
		api.post('super_admin/access_tokens', { json: data }).json(),
	deleteAccessToken: (id: number) => api.delete(`super_admin/access_tokens/${id}`).json(),

	// Installation Configs
	getInstallationConfigs: (params?: Record<string, unknown>) =>
		api.get('super_admin/installation_configs', { searchParams: params }).json(),
	getInstallationConfig: (id: number) => api.get(`super_admin/installation_configs/${id}`).json(),
	updateInstallationConfig: (id: number, data: unknown) =>
		api.patch(`super_admin/installation_configs/${id}`, { json: data }).json(),

	// Account Users
	getAccountUsers: (params?: Record<string, unknown>) =>
		api.get('super_admin/account_users', { searchParams: params }).json(),
	createAccountUser: (data: unknown) =>
		api.post('super_admin/account_users', { json: data }).json(),
	updateAccountUser: (id: number, data: unknown) =>
		api.put(`super_admin/account_users/${id}`, { json: data }).json(),
	deleteAccountUser: (id: number) => api.delete(`super_admin/account_users/${id}`).json(),

	// Audit Logs
	getAuditLogs: (params?: Record<string, unknown>) =>
		api.get('super_admin/audit_logs', { searchParams: params }).json(),

	// Cache
	clearCache: (type?: string) => {
		if (type) {
			return api.post(`super_admin/cache/clear/${type}`).json();
		}
		return api.post('super_admin/cache/clear').json();
	}
};

// Authentication API endpoints
export const authApi = {
	login: (email: string, password: string) =>
		api.post('login', { json: { email, password } }).json<{ token: string; user: unknown }>(),
	logout: () => api.post('logout').json(),
	getCurrentUser: () => api.get('me').json()
};

// Onboarding API endpoints
export const onboardingApi = {
	checkOnboardingStatus: () => api.get('installation/onboarding/status').json(),
	completeOnboarding: (data: unknown) => api.post('installation/onboarding', { json: data }).json()
};
