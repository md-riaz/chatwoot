import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as teamsAPI from '$lib/api/teams';
import type {
  Team,
  TeamMember,
  TeamListParams,
  CreateTeamParams,
  UpdateTeamParams,
} from '$lib/api/teams';

/**
 * Teams Store using Svelte 5 Runes
 * Manages team data and operations
 */
class TeamsStore {
  // Reactive state using $state rune
  allTeams = $state<Team[]>([]);
  selectedTeamId = $state<number | null>(null);
  teamMembers = $state<Map<number, TeamMember[]>>(new Map());
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);
  uiFlags = $state({
    isFetching: false,
    isFetchingItem: false,
    isCreating: false,
    isUpdating: false,
    isDeleting: false,
    isFetchingMembers: false,
    isUpdatingMembers: false,
  });

  // Computed values using $derived rune
  selectedTeam = $derived(
    this.allTeams.find((team) => team.id === this.selectedTeamId) || null
  );

  selectedTeamMembers = $derived(
    this.selectedTeamId ? this.teamMembers.get(this.selectedTeamId) || [] : []
  );

  // Getter for current account ID from route
  get currentAccountId(): number {
    const pageStore = get(page);
    return Number(pageStore.params.accountId);
  }

  // Getter for sorted teams (alphabetically by name)
  get sortedTeams(): Team[] {
    if (!Array.isArray(this.allTeams)) {
      console.error('TeamsStore: allTeams is not an array in sortedTeams', this.allTeams);
      return [];
    }
    return [...this.allTeams].sort((a, b) => {
      const nameA = a.name?.toLowerCase() || '';
      const nameB = b.name?.toLowerCase() || '';
      return nameA.localeCompare(nameB);
    });
  }

  // Getter for teams count
  get teamsCount(): number {
    return Array.isArray(this.allTeams) ? this.allTeams.length : 0;
  }

  // Getter for user's teams
  get myTeams(): Team[] {
    if (!Array.isArray(this.allTeams)) {
      console.error('TeamsStore: allTeams is not an array in myTeams', this.allTeams);
      return [];
    }
    return this.allTeams.filter((team) => team.isMember);
  }

  /**
   * Fetch all teams
   */
  async fetchTeams(params?: TeamListParams): Promise<void> {
    const accountId = this.currentAccountId;
    if (!accountId) return;

    this.uiFlags.isFetching = true;
    this.error = null;

    try {
      const teams = await teamsAPI.getTeams(accountId, params);
      if (Array.isArray(teams)) {
        this.allTeams = teams;
      } else {
        console.error('TeamsStore: fetchTeams returned non-array data', teams);
        this.allTeams = [];
        this.error = 'Received invalid data format for teams';
      }
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch teams';
      console.error('Error fetching teams:', err);
    } finally {
      this.uiFlags.isFetching = false;
    }
  }

  /**
   * Fetch single team
   */
  async fetchTeam(teamId: number): Promise<void> {
    const accountId = this.currentAccountId;
    if (!accountId) return;

    this.uiFlags.isFetchingItem = true;
    this.error = null;

    try {
      const team = await teamsAPI.getTeam(accountId, teamId);
      this.addOrUpdateTeam(team);
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch team';
      console.error('Error fetching team:', err);
    } finally {
      this.uiFlags.isFetchingItem = false;
    }
  }

  /**
   * Create new team
   */
  async createTeam(data: CreateTeamParams): Promise<Team | null> {
    const accountId = this.currentAccountId;
    if (!accountId) return null;

    this.uiFlags.isCreating = true;
    this.error = null;

    try {
      const team = await teamsAPI.createTeam(accountId, data);
      this.allTeams = [...this.allTeams, team];
      return team;
    } catch (err: any) {
      this.error = err.message || 'Failed to create team';
      console.error('Error creating team:', err);
      return null;
    } finally {
      this.uiFlags.isCreating = false;
    }
  }

  /**
   * Update team
   */
  async updateTeam(id: number, data: UpdateTeamParams): Promise<Team | null> {
    const accountId = this.currentAccountId;
    if (!accountId) return null;

    this.uiFlags.isUpdating = true;
    this.error = null;

    try {
      const updatedTeam = await teamsAPI.updateTeam(accountId, id, data);
      this.addOrUpdateTeam(updatedTeam);
      return updatedTeam;
    } catch (err: any) {
      this.error = err.message || 'Failed to update team';
      console.error('Error updating team:', err);
      return null;
    } finally {
      this.uiFlags.isUpdating = false;
    }
  }

  /**
   * Delete team
   */
  async deleteTeam(teamId: number): Promise<boolean> {
    this.uiFlags.isDeleting = true;
    this.error = null;

    // Optimistic update
    const previousTeams = this.allTeams;
    this.allTeams = this.allTeams.filter((team) => team.id !== teamId);

    try {
      await teamsAPI.deleteTeam(teamId);
      
      // Remove team members from cache
      this.teamMembers.delete(teamId);
      
      return true;
    } catch (err: any) {
      // Rollback on error
      this.allTeams = previousTeams;
      this.error = err.message || 'Failed to delete team';
      console.error('Error deleting team:', err);
      return false;
    } finally {
      this.uiFlags.isDeleting = false;
    }
  }

  /**
   * Fetch team members
   */
  async fetchTeamMembers(teamId: number): Promise<void> {
    this.uiFlags.isFetchingMembers = true;
    this.error = null;

    try {
      const members = await teamsAPI.getTeamMembers(teamId);
      this.teamMembers.set(teamId, members);
      this.teamMembers = new Map(this.teamMembers);
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch team members';
      console.error('Error fetching team members:', err);
    } finally {
      this.uiFlags.isFetchingMembers = false;
    }
  }

  /**
   * Add agent to team
   */
  async addTeamMember(teamId: number, agentId: number): Promise<boolean> {
    this.error = null;

    try {
      await teamsAPI.addTeamMember(teamId, agentId);
      
      // Refresh team members
      await this.fetchTeamMembers(teamId);
      
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to add team member';
      console.error('Error adding team member:', err);
      return false;
    }
  }

  /**
   * Remove agent from team
   */
  async removeTeamMember(teamId: number, agentId: number): Promise<boolean> {
    this.error = null;

    try {
      await teamsAPI.removeTeamMember(teamId, agentId);
      
      // Refresh team members
      await this.fetchTeamMembers(teamId);
      
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to remove team member';
      console.error('Error removing team member:', err);
      return false;
    }
  }

  /**
   * Update team members (bulk operation)
   */
  async updateTeamMembers(teamId: number, agentIds: number[]): Promise<boolean> {
    this.uiFlags.isUpdatingMembers = true;
    this.error = null;

    try {
      await teamsAPI.updateTeamMembers(teamId, agentIds);
      
      // Refresh team members
      await this.fetchTeamMembers(teamId);
      
      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to update team members';
      console.error('Error updating team members:', err);
      return false;
    } finally {
      this.uiFlags.isUpdatingMembers = false;
    }
  }

  /**
   * Add or update team in store (used by WebSocket events)
   */
  addOrUpdateTeam(team: Team): void {
    const index = this.allTeams.findIndex((t) => t.id === team.id);
    if (index !== -1) {
      this.allTeams[index] = team;
      this.allTeams = [...this.allTeams];
    } else {
      this.allTeams = [...this.allTeams, team];
    }
  }

  /**
   * Remove team from store (used by WebSocket events)
   */
  removeTeam(teamId: number): void {
    this.allTeams = this.allTeams.filter((team) => team.id !== teamId);
    this.teamMembers.delete(teamId);
  }

  /**
   * Select team
   */
  selectTeam(teamId: number | null): void {
    this.selectedTeamId = teamId;
  }

  /**
   * Clear all teams
   */
  clearTeams(): void {
    this.allTeams = [];
    this.selectedTeamId = null;
    this.teamMembers.clear();
  }

  /**
   * Reset store to initial state
   */
  reset(): void {
    this.allTeams = [];
    this.selectedTeamId = null;
    this.teamMembers = new Map();
    this.isLoading = false;
    this.error = null;
    this.uiFlags = {
      isFetching: false,
      isFetchingItem: false,
      isCreating: false,
      isUpdating: false,
      isDeleting: false,
      isFetchingMembers: false,
      isUpdatingMembers: false,
    };
  }
}

// Export singleton instance
export const teamsStore = new TeamsStore();
