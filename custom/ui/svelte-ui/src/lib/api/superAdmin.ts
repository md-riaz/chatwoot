/**
 * SuperAdmin API Client
 * Handles all SuperAdmin-related API calls for platform management
 */

import api from './client';
import type { ApiResponse } from './types';

// SuperAdmin-specific types
export interface Account {
  id: number;
  name: string;
  locale?: string;
  domain?: string;
  support_email?: string;
  auto_resolve_duration?: number | null;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  email: string;
  name: string;
  display_name?: string;
  avatar_url?: string;
  role?: string;
  confirmed?: boolean;
  locked?: boolean;
  created_at: string;
  updated_at: string;
}

export interface DashboardData {
  accounts_count: number;
  users_count: number;
  conversations_count: number;
  messages_count: number;
  agents_count: number;
  recent_accounts: Account[];
  recent_users: User[];
}

export interface AgentBot {
  id: number;
  name: string;
  description?: string;
  outgoing_url?: string;
  bot_type?: string;
  bot_config?: Record<string, unknown>;
  created_at: string;
  updated_at: string;
}

export interface PlatformApp {
  id: number;
  name: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface AccessToken {
  id: number;
  name: string;
  token: string;
  created_at: string;
}

export interface InstallationConfig {
  id: number;
  name: string;
  locked: boolean;
  value: string | null;
  created_at: string;
  updated_at: string;
}

export interface AccountUser {
  id: number;
  user_id: number;
  account_id: number;
  role: string;
  created_at: string;
  updated_at: string;
}

export interface AuditLog {
  id: number;
  auditable_type: string;
  auditable_id: number;
  associated_type?: string;
  associated_id?: number;
  user_id?: number;
  user_type?: string;
  username?: string;
  action: string;
  audited_changes?: Record<string, unknown>;
  version: number;
  comment?: string;
  remote_address?: string;
  request_uuid?: string;
  created_at: string;
}

export interface Setting {
  id?: number;
  name: string;
  value?: string | null;
  locked?: boolean;
  created_at?: string;
  updated_at?: string;
}

export interface PaginationParams {
  page?: number;
  per_page?: number;
  sort_by?: string;
  order?: 'asc' | 'desc';
}

/**
 * SuperAdmin API methods
 */
export const superAdminApi = {
  // Dashboard
  getDashboard: async (): Promise<DashboardData> => {
    return api.get('super_admin/dashboard').json();
  },

  getInstanceStatus: async (): Promise<Record<string, unknown>> => {
    return api.get('super_admin/instance_status').json();
  },

  // Accounts
  getAccounts: async (params?: PaginationParams): Promise<{ data: Account[] }> => {
    return api.get('super_admin/accounts', { searchParams: params as Record<string, string> }).json();
  },

  getAccount: async (id: number): Promise<Account> => {
    return api.get(`super_admin/accounts/${id}`).json();
  },

  createAccount: async (data: Partial<Account>): Promise<Account> => {
    return api.post('super_admin/accounts', { json: data }).json();
  },

  updateAccount: async (id: number, data: Partial<Account>): Promise<Account> => {
    return api.put(`super_admin/accounts/${id}`, { json: data }).json();
  },

  deleteAccount: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/accounts/${id}`).json();
  },

  // Users
  getUsers: async (params?: PaginationParams): Promise<{ data: User[] }> => {
    return api.get('super_admin/users', { searchParams: params as Record<string, string> }).json();
  },

  getUser: async (id: number): Promise<User> => {
    return api.get(`super_admin/users/${id}`).json();
  },

  createUser: async (data: Partial<User> & { password: string }): Promise<User> => {
    return api.post('super_admin/users', { json: data }).json();
  },

  updateUser: async (id: number, data: Partial<User>): Promise<User> => {
    return api.put(`super_admin/users/${id}`, { json: data }).json();
  },

  deleteUser: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/users/${id}`).json();
  },

  uploadUserAvatar: async (id: number, file: File): Promise<User> => {
    const formData = new FormData();
    formData.append('avatar', file);
    return api.post(`super_admin/users/${id}/avatar`, { body: formData }).json();
  },

  deleteUserAvatar: async (id: number): Promise<User> => {
    return api.delete(`super_admin/users/${id}/avatar`).json();
  },

  confirmUserEmail: async (id: number): Promise<User> => {
    return api.post(`super_admin/users/${id}/confirm`).json();
  },

  lockUser: async (id: number): Promise<User> => {
    return api.post(`super_admin/users/${id}/lock`).json();
  },

  unlockUser: async (id: number): Promise<User> => {
    return api.post(`super_admin/users/${id}/unlock`).json();
  },

  // Settings
  getSettings: async (): Promise<Setting[]> => {
    return api.get('super_admin/settings').json();
  },

  getSettingsGrouped: async (): Promise<Record<string, Setting[]>> => {
    return api.get('super_admin/settings/show').json();
  },

  updateSettings: async (data: Record<string, unknown>): Promise<{ success: boolean }> => {
    return api.patch('super_admin/settings', { json: data }).json();
  },

  createSetting: async (data: Partial<Setting>): Promise<Setting> => {
    return api.post('super_admin/settings', { json: data }).json();
  },

  deleteSetting: async (name: string): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/settings/${name}`).json();
  },

  // Agent Bots
  getAgentBots: async (params?: PaginationParams): Promise<{ data: AgentBot[] }> => {
    return api.get('super_admin/agent_bots', { searchParams: params as Record<string, string> }).json();
  },

  getAgentBot: async (id: number): Promise<AgentBot> => {
    return api.get(`super_admin/agent_bots/${id}`).json();
  },

  createAgentBot: async (data: Partial<AgentBot>): Promise<AgentBot> => {
    return api.post('super_admin/agent_bots', { json: data }).json();
  },

  updateAgentBot: async (id: number, data: Partial<AgentBot>): Promise<AgentBot> => {
    return api.put(`super_admin/agent_bots/${id}`, { json: data }).json();
  },

  deleteAgentBot: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/agent_bots/${id}`).json();
  },

  // Platform Apps
  getPlatformApps: async (params?: PaginationParams): Promise<{ data: PlatformApp[] }> => {
    return api.get('super_admin/platform_apps', { searchParams: params as Record<string, string> }).json();
  },

  getPlatformApp: async (id: number): Promise<PlatformApp> => {
    return api.get(`super_admin/platform_apps/${id}`).json();
  },

  createPlatformApp: async (data: Partial<PlatformApp>): Promise<PlatformApp> => {
    return api.post('super_admin/platform_apps', { json: data }).json();
  },

  updatePlatformApp: async (id: number, data: Partial<PlatformApp>): Promise<PlatformApp> => {
    return api.put(`super_admin/platform_apps/${id}`, { json: data }).json();
  },

  deletePlatformApp: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/platform_apps/${id}`).json();
  },

  // Access Tokens
  getAccessTokens: async (params?: PaginationParams): Promise<{ data: AccessToken[] }> => {
    return api.get('super_admin/access_tokens', { searchParams: params as Record<string, string> }).json();
  },

  createAccessToken: async (data: { name: string }): Promise<AccessToken> => {
    return api.post('super_admin/access_tokens', { json: data }).json();
  },

  deleteAccessToken: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/access_tokens/${id}`).json();
  },

  // Installation Configs
  getInstallationConfigs: async (params?: PaginationParams): Promise<{ data: InstallationConfig[] }> => {
    return api.get('super_admin/installation_configs', { searchParams: params as Record<string, string> }).json();
  },

  getInstallationConfig: async (id: number): Promise<InstallationConfig> => {
    return api.get(`super_admin/installation_configs/${id}`).json();
  },

  updateInstallationConfig: async (id: number, data: Partial<InstallationConfig>): Promise<InstallationConfig> => {
    return api.patch(`super_admin/installation_configs/${id}`, { json: data }).json();
  },

  // Account Users
  getAccountUsers: async (params?: PaginationParams): Promise<{ data: AccountUser[] }> => {
    return api.get('super_admin/account_users', { searchParams: params as Record<string, string> }).json();
  },

  createAccountUser: async (data: { user_id: number; account_id: number; role: string }): Promise<AccountUser> => {
    return api.post('super_admin/account_users', { json: data }).json();
  },

  updateAccountUser: async (id: number, data: { role?: string }): Promise<AccountUser> => {
    return api.put(`super_admin/account_users/${id}`, { json: data }).json();
  },

  deleteAccountUser: async (id: number): Promise<{ success: boolean }> => {
    return api.delete(`super_admin/account_users/${id}`).json();
  },

  // Audit Logs
  getAuditLogs: async (params?: PaginationParams): Promise<{ data: AuditLog[] }> => {
    return api.get('super_admin/audit_logs', { searchParams: params as Record<string, string> }).json();
  },

  // Cache
  clearCache: async (type?: string): Promise<{ success: boolean }> => {
    if (type) {
      return api.post(`super_admin/cache/clear/${type}`).json();
    }
    return api.post('super_admin/cache/clear').json();
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

// Export authApi for compatibility with SuperAdmin routes
export const authApi = {
  getCurrentUser: async (): Promise<User> => {
    return api.get('me').json();
  }
};

// Export as 'api' for convenience
export { superAdminApi as api };
