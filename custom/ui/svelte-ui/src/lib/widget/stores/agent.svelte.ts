/**
 * Widget Agent Store
 * 
 * Manages agent availability and typing indicators.
 */

import * as agentApi from '../api/agent';
import type { Agent } from '../api/types';

class WidgetAgentStore {
  private agents = $state<Agent[]>([]);
  private typingAgentIds = $state<Set<number>>(new Set());
  private isLoading = $state(false);

  // Getters
  get availableAgents() {
    return this.agents;
  }

  get loading() {
    return this.isLoading;
  }

  // Derived values
  get isAnyAgentOnline() {
    return $derived(
      this.agents.some((agent) => agent.availabilityStatus === 'online')
    );
  }

  get onlineAgents() {
    return $derived(
      this.agents.filter((agent) => agent.availabilityStatus === 'online')
    );
  }

  get isAnyAgentTyping() {
    return $derived(this.typingAgentIds.size > 0);
  }

  get typingAgentNames() {
    return $derived(
      this.agents
        .filter((agent) => this.typingAgentIds.has(agent.id))
        .map((agent) => agent.name)
    );
  }

  // Actions
  async fetchAgents(): Promise<void> {
    this.isLoading = true;

    try {
      const agents = await agentApi.getAvailableAgents();
      this.agents = agents;
    } catch (err) {
      console.error('Failed to fetch agents:', err);
    } finally {
      this.isLoading = false;
    }
  }

  async fetchAvailability(): Promise<boolean> {
    try {
      const result = await agentApi.getAgentAvailability();
      this.agents = result.agents;
      return result.available;
    } catch (err) {
      console.error('Failed to fetch availability:', err);
      return false;
    }
  }

  setTyping(agentId: number, isTyping: boolean) {
    if (isTyping) {
      this.typingAgentIds.add(agentId);
    } else {
      this.typingAgentIds.delete(agentId);
    }
    // Trigger reactivity by creating new Set
    this.typingAgentIds = new Set(this.typingAgentIds);
  }

  updateAgentStatus(agentId: number, status: 'online' | 'offline' | 'busy') {
    this.agents = this.agents.map((agent) =>
      agent.id === agentId ? { ...agent, availabilityStatus: status } : agent
    );
  }

  reset() {
    this.agents = [];
    this.typingAgentIds = new Set();
    this.isLoading = false;
  }
}

export const widgetAgentStore = new WidgetAgentStore();
