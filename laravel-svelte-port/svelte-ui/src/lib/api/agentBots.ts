import { api } from './client';

/**
 * Agent Bot interfaces
 */
export interface AgentBot {
    id: number;
    name: string;
    description?: string;
    outgoing_url: string;
    account_id: number;
    access_token: string;
    bot_type: 'webhook';
    bot_config: Record<string, unknown>;
}

export interface AgentBotListParams {
    page?: number;
    perPage?: number;
}

export interface CreateAgentBotParams {
    name: string;
    description?: string;
    outgoing_url: string;
}

export interface UpdateAgentBotParams {
    name?: string;
    description?: string;
    outgoing_url?: string;
}

/**
 * Get list of agent bots
 */
export async function getAgentBots(
    accountId: number,
    params?: AgentBotListParams
): Promise<AgentBot[]> {
    const searchParams = new URLSearchParams();
    if (params?.page) searchParams.set('page', params.page.toString());
    if (params?.perPage) searchParams.set('per_page', params.perPage.toString());

    const query = searchParams.toString();
    const url = `api/v1/accounts/${accountId}/agent_bots${query ? `?${query}` : ''
        }`;

    const response = await api.get(url).json<{ data: AgentBot[] }>();
    return response.data;
}

/**
 * Get single agent bot by ID
 */
export async function getAgentBot(
    accountId: number,
    botId: number
): Promise<AgentBot> {
    const response = await api
        .get(`api/v1/accounts/${accountId}/agent_bots/${botId}`)
        .json<{ data: AgentBot }>();
    return response.data;
}

/**
 * Create new agent bot
 */
export async function createAgentBot(
    accountId: number,
    data: CreateAgentBotParams
): Promise<AgentBot> {
    const response = await api
        .post(`api/v1/accounts/${accountId}/agent_bots`, { json: data })
        .json<{ data: AgentBot }>();
    return response.data;
}

/**
 * Update agent bot
 */
export async function updateAgentBot(
    accountId: number,
    botId: number,
    data: UpdateAgentBotParams
): Promise<AgentBot> {
    const response = await api
        .patch(`api/v1/accounts/${accountId}/agent_bots/${botId}`, { json: data })
        .json<{ data: AgentBot }>();
    return response.data;
}

/**
 * Delete agent bot
 */
export async function deleteAgentBot(
    accountId: number,
    botId: number
): Promise<void> {
    return api
        .delete(`api/v1/accounts/${accountId}/agent_bots/${botId}`)
        .json();
}
