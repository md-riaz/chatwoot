/**
 * SuperAdmin API Client
 * Handles all SuperAdmin-related API calls for platform management
 */

import api from './client';

// SuperAdmin-specific types
export interface Account {
  id: number;
  name: string;
  locale?: string;
  domain?: string;
  supportEmail?: string;
  autoResolveDuration?: number | null;
  status?: 'active' | 'suspended';
  usersCount?: number;
  inboxesCount?: number;
  conversationsCount?: number;
  contactsCount?: number;
  selectedFeatureFlags?: string[];
  allFeatures?: Record<string, boolean>;
  features?: Record<string, boolean>;
  settings?: Record<string, any>;
  limits?: Record<string, any>;
  customAttributes?: Record<string, any>;
  internalAttributes?: Record<string, any>;
  accountUsers?: AccountUser[];
  createdAt: string;
  updatedAt: string;
}

export interface AccountsListResponse {
  data: Account[];
  meta: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

export interface UsersListResponse {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

export interface User {
  id: number;
  email: string;
  name: string;
  displayName?: string;
  phoneNumber?: string;
  avatarUrl?: string;
  availability?: string;
  emailVerifiedAt?: string;
  role?: string;
  roles?: string[];
  confirmed?: boolean;
  confirmedAt?: string; // Rails parity: Field::DateTime
  locked?: boolean;
  customAttributes?: Record<string, any>;
  createdAt: string;
  updatedAt: string;
  accountsCount?: number;
  accounts?: Account[];
  accountUsers?: AccountUser[];
}

export interface DashboardData {
  accountsCount: string;
  usersCount: string;
  conversationsCount: string;
  inboxesCount: string;
  chartData: [string, number][];
}

export interface DashboardResponse {
  data: DashboardData;
}

export interface AgentBot {
  id: number;
  name: string;
  description?: string;
  outgoingUrl?: string;
  botType?: string;
  botConfig?: Record<string, unknown>;
  createdAt: string;
  updatedAt: string;
}

export interface PlatformApp {
  id: number;
  name: string;
  description?: string;
  createdAt: string;
  updatedAt: string;
}

export interface AccessToken {
  id: number;
  name: string;
  token: string;
  createdAt: string;
}

export interface InstallationConfig {
  id: number;
  name: string;
  locked: boolean;
  value: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface AccountUser {
  id: number;
  userId: number;
  accountId: number;
  role: number;
  roleName: string;
  availability: number;
  availabilityName: string;
  activeAt?: string | null;
  createdAt: string;
  updatedAt: string;
  user?: {
    id: number;
    name: string;
    email: string;
    displayName?: string;
  };
  inviter?: {
    id: number;
    name: string;
    email: string;
    displayName?: string;
  };
}

export interface AuditLog {
  id: number;
  auditableType: string;
  auditableId: number;
  associatedType?: string;
  associatedId?: number;
  userId?: number;
  userType?: string;
  username?: string;
  action: string;
  auditedChanges?: Record<string, unknown>;
  version: number;
  comment?: string;
  remoteAddress?: string;
  requestUuid?: string;
  createdAt: string;
}

export interface Setting {
  id?: number;
  name: string;
  value?: string | null;
  locked?: boolean;
  createdAt?: string;
  updatedAt?: string;
}

export interface PaginationParams {
  page?: number;
  perPage?: number;
  sortBy?: string;
  order?: 'asc' | 'desc';
  [key: string]: string | number | boolean | undefined;
}

/**
 * SuperAdmin API methods
 */
export const superAdminApi = {
  // Dashboard
  getDashboard: async (): Promise<DashboardData> => {
    const response: DashboardResponse = await api.get('api/v1/super_admin/dashboard').json();
    return response.data;
  },

  getInstanceStatus: async (): Promise<Record<string, unknown>> => {
    return api.get('api/v1/super_admin/instance_status').json();
  },

  // Accounts
  getAccounts: async (params?: PaginationParams): Promise<AccountsListResponse> => {
    return api.get('api/v1/super_admin/accounts', { searchParams: params as Record<string, string> }).json();
  },

  getAccount: async (id: number): Promise<Account> => {
    const response = await api.get(`api/v1/super_admin/accounts/${id}`).json<{ data: Account }>();
    return response.data;
  },

  createAccount: async (data: Partial<Account>): Promise<Account> => {
    const response = await api.post('api/v1/super_admin/accounts', { json: data }).json<{ data: Account }>();
    return response.data;
  },

  updateAccount: async (id: number, data: Partial<Account>): Promise<Account> => {
    const response = await api.put(`api/v1/super_admin/accounts/${id}`, { json: data }).json<{ data: Account }>();
    return response.data;
  },

  deleteAccount: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`api/v1/super_admin/accounts/${id}`).json();
  },

  seedAccount: async (id: number): Promise<{ message: string }> => {
    return api.post(`api/v1/super_admin/accounts/${id}/seed`).json();
  },

  resetAccountCache: async (id: number): Promise<{ message: string }> => {
    return api.post(`api/v1/super_admin/accounts/${id}/reset_cache`).json();
  },

  // Users
  getUsers: async (params?: PaginationParams): Promise<UsersListResponse> => {
    return api.get('api/v1/super_admin/users', { searchParams: params as Record<string, string> }).json();
  },

  getUser: async (id: number): Promise<User> => {
    const response = await api.get(`api/v1/super_admin/users/${id}`).json<{ data: User }>();
    return response.data;
  },

  createUser: async (data: Partial<User> & { password: string }): Promise<User> => {
    const response = await api.post('api/v1/super_admin/users', { json: data }).json<{ data: User }>();
    return response.data;
  },

  updateUser: async (id: number, data: Partial<User>): Promise<User> => {
    const response = await api.put(`api/v1/super_admin/users/${id}`, { json: data }).json<{ data: User }>();
    return response.data;
  },

  deleteUser: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`api/v1/super_admin/users/${id}`).json();
  },

  uploadUserAvatar: async (id: number, file: File): Promise<User> => {
    const formData = new FormData();
    formData.append('avatar', file);
    const response = await api.post(`api/v1/super_admin/users/${id}/avatar`, { body: formData }).json<{ data: User }>();
    return response.data;
  },

  deleteUserAvatar: async (id: number): Promise<{ message: string }> => {
    return api.delete(`api/v1/super_admin/users/${id}/avatar`).json();
  },

  lockUser: async (id: number): Promise<User> => {
    const response = await api.post(`api/v1/super_admin/users/${id}/lock`).json<{ data: User }>();
    return response.data;
  },

  unlockUser: async (id: number): Promise<User> => {
    const response = await api.post(`api/v1/super_admin/users/${id}/unlock`).json<{ data: User }>();
    return response.data;
  },

  // Settings
  getSettings: async (): Promise<Setting[]> => {
    return api.get('api/v1/super_admin/settings').json();
  },

  getSettingsGrouped: async (): Promise<Record<string, Setting[]>> => {
    return api.get('api/v1/super_admin/settings/show').json();
  },

  updateSettings: async (data: Record<string, unknown>): Promise<{ success: boolean }> => {
    return api.patch('api/v1/super_admin/settings', { json: data }).json();
  },

  createSetting: async (data: Partial<Setting>): Promise<Setting> => {
    return api.post('api/v1/super_admin/settings', { json: data }).json();
  },

  deleteSetting: async (name: string): Promise<{ success: boolean }> => {
    return api.delete(`api/v1/super_admin/settings/${name}`).json();
  },

  // Agent Bots
  getAgentBots: async (params?: PaginationParams): Promise<{ data: AgentBot[] }> => {
    return api.get('api/v1/super_admin/agent_bots', { searchParams: params as Record<string, string> }).json();
  },

  getAgentBot: async (id: number): Promise<AgentBot> => {
    return api.get(`api/v1/super_admin/agent_bots/${id}`).json();
  },

  createAgentBot: async (data: Partial<AgentBot>): Promise<AgentBot> => {
    return api.post('api/v1/super_admin/agent_bots', { json: data }).json();
  },

  updateAgentBot: async (id: number, data: Partial<AgentBot>): Promise<AgentBot> => {
    return api.put(`api/v1/super_admin/agent_bots/${id}`, { json: data }).json();
  },

  deleteAgentBot: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`api/v1/super_admin/agent_bots/${id}`).json();
  },

  // Platform Apps
  getPlatformApps: async (params?: PaginationParams): Promise<{ data: PlatformApp[] }> => {
    return api.get('api/v1/super_admin/platform_apps', { searchParams: params as Record<string, string> }).json();
  },

  getPlatformApp: async (id: number): Promise<PlatformApp> => {
    return api.get(`api/v1/super_admin/platform_apps/${id}`).json();
  },

  createPlatformApp: async (data: Partial<PlatformApp>): Promise<PlatformApp> => {
    return api.post('api/v1/super_admin/platform_apps', { json: data }).json();
  },

  updatePlatformApp: async (id: number, data: Partial<PlatformApp>): Promise<PlatformApp> => {
    return api.put(`api/v1/super_admin/platform_apps/${id}`, { json: data }).json();
  },

  deletePlatformApp: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`api/v1/super_admin/platform_apps/${id}`).json();
  },

  // Access Tokens
  getAccessTokens: async (params?: PaginationParams): Promise<{ data: AccessToken[] }> => {
    return api.get('api/v1/super_admin/access_tokens', { searchParams: params as Record<string, string> }).json();
  },

  createAccessToken: async (data: { name: string }): Promise<AccessToken> => {
    return api.post('api/v1/super_admin/access_tokens', { json: data }).json();
  },

  deleteAccessToken: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`api/v1/super_admin/access_tokens/${id}`).json();
  },

  // Installation Configs
  getInstallationConfigs: async (params?: PaginationParams): Promise<{ data: InstallationConfig[] }> => {
    return api.get('api/v1/super_admin/installation_configs', { searchParams: params as Record<string, string> }).json();
  },

  getInstallationConfig: async (id: number): Promise<InstallationConfig> => {
    return api.get(`api/v1/super_admin/installation_configs/${id}`).json();
  },

  updateInstallationConfig: async (id: number, data: Partial<InstallationConfig>): Promise<InstallationConfig> => {
    return api.patch(`api/v1/super_admin/installation_configs/${id}`, { json: data }).json();
  },

  // Account Users
  getAccountUsers: async (params?: PaginationParams): Promise<{ data: AccountUser[] }> => {
    return api.get('api/v1/super_admin/account_users', { searchParams: params as Record<string, string> }).json();
  },

  createAccountUser: async (data: { userId: number; accountId: number; role: string }): Promise<AccountUser> => {
    return api.post('api/v1/super_admin/account_users', { json: data }).json();
  },

  updateAccountUser: async (id: number, data: { role?: string }): Promise<AccountUser> => {
    return api.put(`api/v1/super_admin/account_users/${id}`, { json: data }).json();
  },

  deleteAccountUser: async (id: number): Promise<{ message: string }> => {
    return api.delete(`api/v1/super_admin/account_users/${id}`).json();
  },

  // Audit Logs
  getAuditLogs: async (params?: PaginationParams): Promise<{ data: AuditLog[] }> => {
    return api.get('api/v1/super_admin/audit_logs', { searchParams: params as Record<string, string> }).json();
  },

  // Cache
  clearCache: async (type?: string): Promise<{ success: boolean }> => {
    if (type) {
      return api.post(`api/v1/super_admin/cache/clear/${type}`).json();
    }
    return api.post('api/v1/super_admin/cache/clear').json();
  }
};

// Simplified API export for components (adds convenience wrappers)
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

// Export authApi for compatibility with SuperAdmin routes
export const authApi = {
  getCurrentUser: async (): Promise<User> => {
    const response = await api.get('api/v1/auth/me').json<{ data: User }>();
    return response.data;
  }
};

// Export as 'api' for convenience
export { superAdminApi as api };

