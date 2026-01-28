import { api } from './client';
import type { PaginatedResponse } from './types';
import type { Contact } from './contacts';

export interface Segment {
    id: number;
    name: string;
    query: Record<string, any>;
    account_id: number;
    created_at: string;
    updated_at: string;
}

export async function getSegments(accountId: number): Promise<Segment[]> {
    return api.get(`api/v1/accounts/${accountId}/segments`).json();
}

export async function getSegment(accountId: number, segmentId: number): Promise<Segment> {
    return api.get(`api/v1/accounts/${accountId}/segments/${segmentId}`).json();
}

export interface CreateSegmentParams {
    name: string;
    query: Record<string, any>;
}

export async function createSegment(accountId: number, params: CreateSegmentParams): Promise<Segment> {
    return api.post(`api/v1/accounts/${accountId}/segments`, {
        json: params,
    }).json();
}

export async function getSegmentContacts(
    accountId: number,
    segmentId: number,
    params: Record<string, any> = {}
): Promise<PaginatedResponse<Contact>> {
    return api.get(`api/v1/accounts/${accountId}/segments/${segmentId}/contacts`, {
        searchParams: params
    }).json();
}
