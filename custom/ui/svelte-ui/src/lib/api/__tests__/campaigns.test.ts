/**
 * Tests for Campaigns API
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import * as campaignsApi from '../campaigns';
import { CAMPAIGN_TYPES, CAMPAIGN_STATUS } from '../campaigns';

// Mock the api client
vi.mock('../client', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}));

describe('Campaign Constants', () => {
  it('defines campaign types', () => {
    expect(CAMPAIGN_TYPES.ONGOING).toBe('ongoing');
    expect(CAMPAIGN_TYPES.ONE_OFF).toBe('one_off');
  });

  it('defines campaign statuses', () => {
    expect(CAMPAIGN_STATUS.ACTIVE).toBe('active');
    expect(CAMPAIGN_STATUS.PAUSED).toBe('paused');
    expect(CAMPAIGN_STATUS.COMPLETED).toBe('completed');
  });
});

describe('Campaign Interfaces', () => {
  it('defines Campaign interface structure', () => {
    const campaign: campaignsApi.Campaign = {
      id: 1,
      title: 'Test Campaign',
      message: 'Test message',
      campaignType: 'ongoing',
      campaignStatus: 'active',
      audience: [],
      enabled: true,
      triggerRules: {},
      inboxId: 1,
      createdAt: '2024-01-01',
      updatedAt: '2024-01-01',
    };

    expect(campaign.id).toBe(1);
    expect(campaign.title).toBe('Test Campaign');
    expect(campaign.campaignType).toBe('ongoing');
  });

  it('defines CreateCampaignParams interface', () => {
    const params: campaignsApi.CreateCampaignParams = {
      title: 'New Campaign',
      message: 'Message',
      campaignType: 'ongoing',
      inboxId: 1,
      enabled: true,
    };

    expect(params.title).toBe('New Campaign');
    expect(params.inboxId).toBe(1);
  });
});

describe('Campaign API Functions', () => {
  it('exports getCampaigns function', () => {
    expect(typeof campaignsApi.getCampaigns).toBe('function');
  });

  it('exports getCampaign function', () => {
    expect(typeof campaignsApi.getCampaign).toBe('function');
  });

  it('exports createCampaign function', () => {
    expect(typeof campaignsApi.createCampaign).toBe('function');
  });

  it('exports updateCampaign function', () => {
    expect(typeof campaignsApi.updateCampaign).toBe('function');
  });

  it('exports deleteCampaign function', () => {
    expect(typeof campaignsApi.deleteCampaign).toBe('function');
  });

  it('exports toggleCampaignStatus function', () => {
    expect(typeof campaignsApi.toggleCampaignStatus).toBe('function');
  });
});
