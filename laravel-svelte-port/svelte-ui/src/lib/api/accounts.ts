import { api } from './client';
import type { UserAccount } from './auth';

export interface UpdateAccountParams {
  name?: string;
  locale?: string;
  domain?: string;
  supportEmail?: string;
  autoResolveDuration?: number;
  autoResolveMessage?: string;
  autoResolveIgnoreWaiting?: boolean;
  autoResolveLabel?: string;
  audioTranscriptions?: boolean;
  settings?: Record<string, any>; // In case we need to pass generic settings
}

/**
 * Update account details
 */
export async function update(
  accountId: number,
  params: UpdateAccountParams
): Promise<UserAccount> {
  // The client automatically converts camelCase params to snake_case for the request
  const response = await api
    .patch(`api/v1/accounts/${accountId}`, {
      json: params,
    })
    .json<{ data: UserAccount }>();

  return response.data;
}

/**
 * Toggle account deletion
 */
export async function toggleDeletion(
  accountId: number,
  actionType: 'delete' | 'undelete'
): Promise<void> {
  await api.post(`enterprise/api/v1/accounts/${accountId}/toggle_deletion`, {
    json: { action_type: actionType },
  });
}

export interface AccountDetails extends UserAccount {
  limits?: Record<string, unknown>;
  status?: string;
}

/**
 * Get account details
 */
export async function show(accountId: number): Promise<AccountDetails> {
  const response = await api
    .get(`api/v1/accounts/${accountId}`)
    .json<{ data: AccountDetails }>();
  return response.data;
}
