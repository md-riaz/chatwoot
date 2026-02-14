import { api, toSearchParams } from './client';

export interface CannedResponse {
  id: number;
  shortCode: string;
  content: string;
  accountId: number;
  createdAt: string;
  updatedAt: string;
}

export interface CannedResponseListParams {
  page?: number;
  perPage?: number;
  search?: string;
}

interface CannedResponseListMeta {
  currentPage: number;
  lastPage: number;
  perPage: number;
  total: number;
}

export interface CannedResponseListResponse {
  data: CannedResponse[];
  meta: CannedResponseListMeta;
}

export async function getCannedResponses(
  accountId: number,
  params: CannedResponseListParams = {}
): Promise<CannedResponseListResponse> {
  return api
    .get(`api/v1/accounts/${accountId}/canned_responses`, {
      searchParams: toSearchParams(params),
    })
    .json<CannedResponseListResponse>();
}

export async function deleteCannedResponse(
  accountId: number,
  cannedResponseId: number
): Promise<void> {
  await api.delete(
    `api/v1/accounts/${accountId}/canned_responses/${cannedResponseId}`
  );
}
