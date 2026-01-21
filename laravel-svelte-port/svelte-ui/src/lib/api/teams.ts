import { api, toSearchParams } from './client';

/**
 * Team interfaces
 */
export interface Team {
  id: number;
  name: string;
  description: string;
  allowAutoAssign: boolean;
  accountId: number;
  isDefault: boolean;
  isMember: boolean;
  conversationsCount?: number;
}

export interface TeamMember {
  id: number;
  name: string;
  email: string;
  avatarUrl: string;
  role: string;
  availability: string;
}

export interface TeamListParams {
  page?: number;
  perPage?: number;
  [key: string]: string | number | boolean | undefined;
}

export interface CreateTeamParams {
  name: string;
  description?: string;
  allow_auto_assign?: boolean;
}

export interface UpdateTeamParams {
  name?: string;
  description?: string;
  allow_auto_assign?: boolean;
}

/**
 * Get list of teams
 */
export async function getTeams(accountId: number, params?: TeamListParams): Promise<Team[]> {
  const response = await api.get(`api/v1/accounts/${accountId}/teams`, {
    searchParams: toSearchParams(params),
  }).json<{ data: Team[] }>();
  return response.data;
}

/**
 * Get single team by ID
 */
export async function getTeam(accountId: number, teamId: number): Promise<Team> {
  const response = await api.get(`api/v1/accounts/${accountId}/teams/${teamId}`).json<{ data: Team }>();
  return response.data;
}

/**
 * Create new team
 */
export async function createTeam(accountId: number, params: CreateTeamParams): Promise<Team> {
  const response = await api.post(`api/v1/accounts/${accountId}/teams`, {
    json: params,
  }).json<{ data: Team }>();
  return response.data;
}

/**
 * Update team
 */
export async function updateTeam(accountId: number, teamId: number, params: UpdateTeamParams): Promise<Team> {
  const response = await api.patch(`api/v1/accounts/${accountId}/teams/${teamId}`, {
    json: params,
  }).json<{ data: Team }>();
  return response.data;
}

/**
 * Delete team
 */
export async function deleteTeam(accountId: number, teamId: number): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/teams/${teamId}`);
}

/**
 * Get team members
 */
export async function getTeamMembers(accountId: number, teamId: number): Promise<TeamMember[]> {
  const response = await api.get(`api/v1/accounts/${accountId}/teams/${teamId}/team_members`).json<TeamMember[]>();
  return response;
}

/**
 * Add agent to team
 */
export async function addTeamMember(accountId: number, teamId: number, agentId: number): Promise<void> {
  await api.post(`api/v1/accounts/${accountId}/teams/${teamId}/team_members`, {
    json: {
      user_ids: [agentId],
    },
  });
}

/**
 * Remove agent from team
 */
export async function removeTeamMember(accountId: number, teamId: number, agentId: number): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/teams/${teamId}/team_members`, {
    json: {
      user_ids: [agentId],
    },
  });
}

/**
 * Update team members (bulk operation)
 */
export async function updateTeamMembers(accountId: number, teamId: number, agentIds: number[]): Promise<void> {
  await api.patch(`api/v1/accounts/${accountId}/teams/${teamId}/team_members`, {
    json: {
      user_ids: agentIds,
    },
  });
}
