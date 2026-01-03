/**
 * SuperAdmin API Client
 * Handles all SuperAdmin-related API calls for platform management
 */

import { apiClient } from './client';
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
    return apiClient.get('super_admin/dashboard').json();
  },

  getInstanceStatus: async (): Promise<Record<string, unknown>> => {
    return apiClient.get('super_admin/instance_status').json();
  },

  // Accounts
  getAccounts: async (params?: PaginationParams): Promise<{ data: Account[] }> => {
    return apiClient.get('super_admin/accounts', { searchParams: params as Record<string, string> }).json();
  },

  getAccount: async (id: number): Promise<Account> => {
    return apiClient.get(`super_admin/accounts/${id}`).json();
  },

  createAccount: async (data: Partial<Account>): Promise<Account> => {
    return apiClient.post('super_admin/accounts', { json: data }).json();
  },

  updateAccount: async (id: number, data: Partial<Account>): Promise<Account> => {
    return apiClient.put(`super_admin/accounts/${id}`, { json: data }).json();
  },

  deleteAccount: async (id: number): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/accounts/${id}`).json();
  },

  // Users
  getUsers: async (params?: PaginationParams): Promise<{ data: User[] }> => {
    return apiClient.get('super_admin/users', { searchParams: params as Record<string, string> }).json();
  },

  getUser: async (id: number): Promise<User> => {
    return apiClient.get(`super_admin/users/${id}`).json();
  },

  createUser: async (data: Partial<User> & { password: string }): Promise<User> => {
    return apiClient.post('super_admin/users', { json: data }).json();
  },

  updateUser: async (id: number, data: Partial<User>): Promise<User> => {
    return apiClient.put(`super_admin/users/${id}`, { json: data }).json();
  },

  deleteUser: async (id: number): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/users/${id}`).json();
  },

  uploadUserAvatar: async (id: number, file: File): Promise<User> => {
    const formData = new FormData();
    formData.append('avatar', file);
    return apiClient.post(`super_admin/users/${id}/avatar`, { body: formData }).json();
  },

  deleteUserAvatar: async (id: number): Promise<User> => {
    return apiClient.delete(`super_admin/users/${id}/avatar`).json();
  },

  confirmUserEmail: async (id: number): Promise<User> => {
    return apiClient.post(`super_admin/users/${id}/confirm`).json();
  },

  lockUser: async (id: number): Promise<User> => {
    return apiClient.post(`super_admin/users/${id}/lock`).json();
  },

  unlockUser: async (id: number): Promise<User> => {
    return apiClient.post(`super_admin/users/${id}/unlock`).json();
  },

  // Settings
  getSettings: async (): Promise<Setting[]> => {
    return apiClient.get('super_admin/settings').json();
  },

  getSettingsGrouped: async (): Promise<Record<string, Setting[]>> => {
    return apiClient.get('super_admin/settings/show').json();
  },

  updateSettings: async (data: Record<string, unknown>): Promise<{ success: boolean }> => {
    return apiClient.patch('super_admin/settings', { json: data }).json();
  },

  createSetting: async (data: Partial<Setting>): Promise<Setting> => {
    return apiClient.post('super_admin/settings', { json: data }).json();
  },

  deleteSetting: async (name: string): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/settings/${name}`).json();
  },

  // Agent Bots
  getAgentBots: async (params?: PaginationParams): Promise<{ data: AgentBot[] }> => {
    return apiClient.get('super_admin/agent_bots', { searchParams: params as Record<string, string> }).json();
  },

  getAgentBot: async (id: number): Promise<AgentBot> => {
    return apiClient.get(`super_admin/agent_bots/${id}`).json();
  },

  createAgentBot: async (data: Partial<AgentBot>): Promise<AgentBot> => {
    return apiClient.post('super_admin/agent_bots', { json: data }).json();
  },

  updateAgentBot: async (id: number, data: Partial<AgentBot>): Promise<AgentBot> => {
    return apiClient.put(`super_admin/agent_bots/${id}`, { json: data }).json();
  },

  deleteAgentBot: async (id: number): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/agent_bots/${id}`).json();
  },

  // Platform Apps
  getPlatformApps: async (params?: PaginationParams): Promise<{ data: PlatformApp[] }> => {
    return apiClient.get('super_admin/platform_apps', { searchParams: params as Record<string, string> }).json();
  },

  getPlatformApp: async (id: number): Promise<PlatformApp> => {
    return apiClient.get(`super_admin/platform_apps/${id}`).json();
  },

  createPlatformApp: async (data: Partial<PlatformApp>): Promise<PlatformApp> => {
    return apiClient.post('super_admin/platform_apps', { json: data }).json();
  },

  updatePlatformApp: async (id: number, data: Partial<PlatformApp>): Promise<PlatformApp> => {
    return apiClient.put(`super_admin/platform_apps/${id}`, { json: data }).json();
  },

  deletePlatformApp: async (id: number): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/platform_apps/${id}`).json();
  },

  // Access Tokens
  getAccessTokens: async (params?: PaginationParams): Promise<{ data: AccessToken[] }> => {
    return apiClient.get('super_admin/access_tokens', { searchParams: params as Record<string, string> }).json();
  },

  createAccessToken: async (data: { name: string }): Promise<AccessToken> => {
    return apiClient.post('super_admin/access_tokens', { json: data }).json();
  },

  deleteAccessToken: async (id: number): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/access_tokens/${id}`).json();
  },

  // Installation Configs
  getInstallationConfigs: async (params?: PaginationParams): Promise<{ data: InstallationConfig[] }> => {
    return apiClient.get('super_admin/installation_configs', { searchParams: params as Record<string, string> }).json();
  },

  getInstallationConfig: async (id: number): Promise<InstallationConfig> => {
    return apiClient.get(`super_admin/installation_configs/${id}`).json();
  },

  updateInstallationConfig: async (id: number, data: Partial<InstallationConfig>): Promise<InstallationConfig> => {
    return apiClient.patch(`super_admin/installation_configs/${id}`, { json: data }).json();
  },

  // Account Users
  getAccountUsers: async (params?: PaginationParams): Promise<{ data: AccountUser[] }> => {
    return apiClient.get('super_admin/account_users', { searchParams: params as Record<string, string> }).json();
  },

  createAccountUser: async (data: { user_id: number; account_id: number; role: string }): Promise<AccountUser> => {
    return apiClient.post('super_admin/account_users', { json: data }).json();
  },

  updateAccountUser: async (id: number, data: { role?: string }): Promise<AccountUser> => {
    return apiClient.put(`super_admin/account_users/${id}`, { json: data }).json();
  },

  deleteAccountUser: async (id: number): Promise<{ success: boolean }> => {
    return apiClient.delete(`super_admin/account_users/${id}`).json();
  },

  // Audit Logs
  getAuditLogs: async (params?: PaginationParams): Promise<{ data: AuditLog[] }> => {
    return apiClient.get('super_admin/audit_logs', { searchParams: params as Record<string, string> }).json();
  },

  // Cache
  clearCache: async (type?: string): Promise<{ success: boolean }> => {
    if (type) {
      return apiClient.post(`super_admin/cache/clear/${type}`).json();
    }
    return apiClient.post('super_admin/cache/clear').json();
  }
};

// Export authApi for compatibility with SuperAdmin routes
export const authApi = {
  getCurrentUser: async (): Promise<User> => {
    return apiClient.get('me').json();
  }
};
