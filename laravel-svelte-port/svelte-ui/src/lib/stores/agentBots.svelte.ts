import { page } from '$app/stores';
import { get } from 'svelte/store';
import * as agentBotsAPI from '$lib/api/agentBots';
import type {
    AgentBot,
    CreateAgentBotParams,
    UpdateAgentBotParams,
} from '$lib/api/agentBots';

/**
 * Agent Bots Store using Svelte 5 Runes
 */
class AgentBotsStore {
    // Reactive state
    allBots = $state<AgentBot[]>([]);
    isLoading = $state<boolean>(false);
    error = $state<string | null>(null);

    uiFlags = $state({
        isFetching: false,
        isCreating: false,
        isUpdating: false,
        isDeleting: false,
    });

    // Getters
    get currentAccountId(): number {
        const pageStore = get(page);
        return Number(pageStore.params.accountId);
    }

    get botsCount(): number {
        return Array.isArray(this.allBots) ? this.allBots.length : 0;
    }

    /**
     * Fetch all agent bots
     */
    async fetchAgentBots(): Promise<void> {
        const accountId = this.currentAccountId;
        if (!accountId) return;

        this.uiFlags.isFetching = true;
        this.error = null;

        try {
            const bots = await agentBotsAPI.getAgentBots(accountId);
            if (Array.isArray(bots)) {
                this.allBots = bots;
            } else {
                this.allBots = [];
                console.error('AgentBotsStore: fetch returned non-array data', bots);
            }
        } catch (err: any) {
            this.error = err.message || 'Failed to fetch agent bots';
            console.error('Error fetching agent bots:', err);
        } finally {
            this.uiFlags.isFetching = false;
        }
    }

    /**
     * Create agent bot
     */
    async createAgentBot(data: CreateAgentBotParams): Promise<AgentBot | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isCreating = true;
        this.error = null;

        try {
            const bot = await agentBotsAPI.createAgentBot(accountId, data);
            this.allBots = [...this.allBots, bot];
            return bot;
        } catch (err: any) {
            this.error = err.message || 'Failed to create agent bot';
            console.error('Error creating agent bot:', err);
            return null;
        } finally {
            this.uiFlags.isCreating = false;
        }
    }

    /**
     * Update agent bot
     */
    async updateAgentBot(
        botId: number,
        data: UpdateAgentBotParams
    ): Promise<AgentBot | null> {
        const accountId = this.currentAccountId;
        if (!accountId) return null;

        this.uiFlags.isUpdating = true;
        this.error = null;

        try {
            const updatedBot = await agentBotsAPI.updateAgentBot(accountId, botId, data);

            const index = this.allBots.findIndex(b => b.id === botId);
            if (index !== -1) {
                this.allBots[index] = updatedBot;
                // Trigger reactivity
                this.allBots = [...this.allBots];
            }
            return updatedBot;
        } catch (err: any) {
            this.error = err.message || 'Failed to update agent bot';
            console.error('Error updating agent bot:', err);
            return null;
        } finally {
            this.uiFlags.isUpdating = false;
        }
    }

    /**
     * Delete agent bot
     */
    async deleteAgentBot(botId: number): Promise<boolean> {
        const accountId = this.currentAccountId;
        if (!accountId) return false;

        this.uiFlags.isDeleting = true;
        this.error = null;

        // Optimistic update
        const previousBots = this.allBots;
        this.allBots = this.allBots.filter(b => b.id !== botId);

        try {
            await agentBotsAPI.deleteAgentBot(accountId, botId);
            return true;
        } catch (err: any) {
            // Rollback
            this.allBots = previousBots;
            this.error = err.message || 'Failed to delete agent bot';
            console.error('Error deleting agent bot:', err);
            return false;
        } finally {
            this.uiFlags.isDeleting = false;
        }
    }
}

export const agentBotsStore = new AgentBotsStore();
