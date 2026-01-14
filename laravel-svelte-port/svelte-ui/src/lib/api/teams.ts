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
export async function getTeams(params?: TeamListParams): Promise<Team[]> {
  const response = await api.get('teams', {
    searchParams: toSearchParams(params),
  }).json<Team[]>();
  return response;
}

/**
 * Get single team by ID
 */
export async function getTeam(teamId: number): Promise<Team> {
  const response = await api.get(`teams/${teamId}`).json<Team>();
  return response;
}

/**
 * Create new team
 */
export async function createTeam(params: CreateTeamParams): Promise<Team> {
  const response = await api.post('teams', {
    json: params,
  }).json<Team>();
  return response;
}

/**
 * Update team
 */
export async function updateTeam(teamId: number, params: UpdateTeamParams): Promise<Team> {
  const response = await api.patch(`teams/${teamId}`, {
    json: params,
  }).json<Team>();
  return response;
}

/**
 * Delete team
 */
export async function deleteTeam(teamId: number): Promise<void> {
  await api.delete(`teams/${teamId}`);
}

/**
 * Get team members
 */
export async function getTeamMembers(teamId: number): Promise<TeamMember[]> {
  const response = await api.get(`teams/${teamId}/team_members`).json<TeamMember[]>();
  return response;
}

/**
 * Add agent to team
 */
export async function addTeamMember(teamId: number, agentId: number): Promise<void> {
  await api.post(`teams/${teamId}/team_members`, {
    json: {
      user_ids: [agentId],
    },
  });
}

/**
 * Remove agent from team
 */
export async function removeTeamMember(teamId: number, agentId: number): Promise<void> {
  await api.delete(`teams/${teamId}/team_members`, {
    json: {
      user_ids: [agentId],
    },
  });
}

/**
 * Update team members (bulk operation)
 */
export async function updateTeamMembers(teamId: number, agentIds: number[]): Promise<void> {
  await api.patch(`teams/${teamId}/team_members`, {
    json: {
      user_ids: agentIds,
    },
  });
}
