import { api as client, toSearchParams } from './client';
import type { ApiResponse } from './types';

export interface CustomView {
  id: number;
  name: string;
  filterType: string; // 'conversation' or 'contact'
  query: any;
  createdAt: string;
  updatedAt: string;
}

export interface CustomViewListParams {
  filterType?: string;
}

interface CustomViewResponse {
  data: CustomView;
}

interface CustomViewListResponse {
  data: CustomView[];
}

export const getCustomViews = async (accountId: number, params?: CustomViewListParams): Promise<CustomView[]> => {
  const response = await client.get(`api/v1/accounts/${accountId}/custom_filters`, { 
    searchParams: toSearchParams(params) 
  }).json<CustomViewListResponse>();
  return response.data;
};

export const getCustomView = async (accountId: number, id: number): Promise<CustomView> => {
  const response = await client.get(`api/v1/accounts/${accountId}/custom_filters/${id}`).json<CustomViewResponse>();
  return response.data;
};

export const createCustomView = async (accountId: number, data: Partial<CustomView>): Promise<CustomView> => {
  const response = await client.post(`api/v1/accounts/${accountId}/custom_filters`, { 
    json: data 
  }).json<CustomViewResponse>();
  return response.data;
};

export const updateCustomView = async (accountId: number, id: number, data: Partial<CustomView>): Promise<CustomView> => {
  const response = await client.patch(`api/v1/accounts/${accountId}/custom_filters/${id}`, { 
    json: data 
  }).json<CustomViewResponse>();
  return response.data;
};

export const deleteCustomView = async (accountId: number, id: number): Promise<void> => {
  await client.delete(`api/v1/accounts/${accountId}/custom_filters/${id}`);
};
