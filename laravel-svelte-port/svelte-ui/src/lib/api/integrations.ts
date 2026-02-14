import { api } from './client';

export interface Integration {
  id: string;
  appId?: string;
  name: string;
  description?: string;
  status: 'connected' | 'available';
  logo?: string;
}

interface IntegrationApiResponse {
  id: string;
  appId?: string;
  name: string;
  description?: string;
  enabled?: boolean;
  logo?: string;
}

export async function getIntegrations(
  accountId: number
): Promise<Integration[]> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/integrations`)
    .json<{ data: IntegrationApiResponse[] }>();

  return (response.data || []).map(integration => ({
    id: integration.id,
    appId: integration.appId,
    name: integration.name,
    description: integration.description,
    logo: integration.logo,
    status: integration.enabled ? 'connected' : 'available',
  }));
}
