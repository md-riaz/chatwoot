import { page } from '$app/state';
import * as agentsApi from '$lib/api/agents';
import type {
  Agent,
  AgentListParams,
  CreateAgentParams,
  UpdateAgentParams,
} from '$lib/api/agents';

/**
 * Agents Store using Svelte 5 runes
 * Manages agent/user state and operations
 */
class AgentsStore {
  // Reactive state using $state rune
  allAgents = $state<Agent[]>([]);
  selectedAgentId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  isCreating = $state<boolean>(false);
  isUpdating = $state<boolean>(false);
  isDeleting = $state<boolean>(false);
  error = $state<string | null>(null);

  // Computed values using $derived rune
  selectedAgent = $derived(
    this.allAgents.find(a => a.id === this.selectedAgentId) || null
  );

  // Computed account ID from route params
  get currentAccountId(): number {
    return parseInt(page.params.accountId || '0', 10);
  }

  get sortedAgents(): Agent[] {
    return [...this.allAgents].sort((a, b) => {
      const nameA = a.name?.toLowerCase() || '';
      const nameB = b.name?.toLowerCase() || '';
      return nameA.localeCompare(nameB);
    });
  }

  get administrators(): Agent[] {
    return this.allAgents.filter(a => a.role === 'administrator');
  }

  get regularAgents(): Agent[] {
    return this.allAgents.filter(a => a.role === 'agent');
  }

  get onlineAgents(): Agent[] {
    return this.allAgents.filter(a => a.availabilityStatus === 'online');
  }

  get agentsCount(): number {
    return this.allAgents.length;
  }

  /**
   * Fetch all agents
   */
  async fetchAgents(params?: AgentListParams): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const agents = await agentsApi.getAgents(this.currentAccountId, params);
      this.allAgents = agents || [];
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch agents';
      console.error('Error fetching agents:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch a single agent
   */
  async fetchAgent(agentId: number): Promise<Agent | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;

      const agent = await agentsApi.getAgent(this.currentAccountId, agentId);

      // Update in the store if it exists
      const index = this.allAgents.findIndex(a => a.id === agent.id);
      if (index !== -1) {
        this.allAgents[index] = agent;
      } else {
        this.allAgents.push(agent);
      }

      return agent;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch agent';
      console.error('Error fetching agent:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Create a new agent
   */
  async createAgent(data: CreateAgentParams): Promise<Agent | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isCreating = true;
      this.error = null;

      const newAgent = await agentsApi.createAgent(this.currentAccountId, data);
      this.allAgents.push(newAgent);
      return newAgent;
    } catch (err: any) {
      this.error = err.message || 'Failed to create agent';
      console.error('Error creating agent:', err);
      throw err;
    } finally {
      this.isCreating = false;
    }
  }

  /**
   * Update an existing agent
   */
  async updateAgent(
    agentId: number,
    data: UpdateAgentParams
  ): Promise<Agent | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isUpdating = true;
      this.error = null;

      const updatedAgent = await agentsApi.updateAgent(
        this.currentAccountId,
        agentId,
        data
      );

      const index = this.allAgents.findIndex(a => a.id === agentId);
      if (index !== -1) {
        this.allAgents[index] = updatedAgent;
      }

      return updatedAgent;
    } catch (err: any) {
      this.error = err.message || 'Failed to update agent';
      console.error('Error updating agent:', err);
      throw err;
    } finally {
      this.isUpdating = false;
    }
  }

  /**
   * Delete an agent
   */
  async deleteAgent(agentId: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      this.isDeleting = true;
      this.error = null;

      await agentsApi.deleteAgent(this.currentAccountId, agentId);

      this.allAgents = this.allAgents.filter(a => a.id !== agentId);
      if (this.selectedAgentId === agentId) {
        this.selectedAgentId = null;
      }

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete agent';
      console.error('Error deleting agent:', err);
      return false;
    } finally {
      this.isDeleting = false;
    }
  }

  /**
   * Add or update agent in store (used by realtime events)
   */
  addOrUpdateAgent(agent: Agent): void {
    const index = this.allAgents.findIndex(
      existingAgent => existingAgent.id === agent.id
    );
    if (index !== -1) {
      this.allAgents[index] = {
        ...this.allAgents[index],
        ...agent,
      };
      return;
    }

    this.allAgents.push(agent);
  }

  /**
   * Update a single agent presence from realtime payload
   */
  updateSingleAgentPresence(
    agentId: number,
    availabilityStatus: Agent['availabilityStatus']
  ): void {
    const index = this.allAgents.findIndex(agent => agent.id === agentId);
    if (index === -1) return;

    this.allAgents[index] = {
      ...this.allAgents[index],
      availabilityStatus,
    };
  }

  /**
   * Select an agent
   */
  selectAgent(agentId: number | null): void {
    this.selectedAgentId = agentId;
  }

  /**
   * Clear all agents
   */
  clear(): void {
    this.allAgents = [];
    this.selectedAgentId = null;
    this.error = null;
  }
}

// Export singleton instance
export const agentsStore = new AgentsStore();
