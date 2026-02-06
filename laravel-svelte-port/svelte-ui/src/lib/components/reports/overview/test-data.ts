/**
 * Test data for reports overview components
 * Used for development and testing until backend APIs are ready
 */

import type { 
  LiveAccountMetric, 
  LiveAgentMetric, 
  LiveTeamMetric, 
  AgentStatusMetric,
  HeatmapData 
} from '$lib/stores/reports.svelte';

export const mockAccountConversationMetric: LiveAccountMetric = {
  open: 42,
  unattended: 15,
  unassigned: 8,
  pending: 23
};

export const mockAgentConversationMetric: LiveAgentMetric[] = [
  { assigneeId: 1, open: 12, unattended: 3 },
  { assigneeId: 2, open: 8, unattended: 2 },
  { assigneeId: 3, open: 5, unattended: 1 }
];

export const mockTeamConversationMetric: LiveTeamMetric[] = [
  { teamId: 1, open: 18, unattended: 4 },
  { teamId: 2, open: 15, unattended: 6 },
  { teamId: 3, open: 9, unattended: 5 }
];

export const mockAgentStatus: AgentStatusMetric = {
  online: 12,
  busy: 5,
  offline: 3
};

export const mockAgents = [
  {
    id: 1,
    name: 'John Doe',
    availableName: 'John Doe',
    email: 'john@example.com',
    thumbnail: '',
    availabilityStatus: 'online' as const
  },
  {
    id: 2,
    name: 'Jane Smith',
    availableName: 'Jane Smith',
    email: 'jane@example.com',
    thumbnail: '',
    availabilityStatus: 'busy' as const
  },
  {
    id: 3,
    name: 'Bob Johnson',
    availableName: 'Bob Johnson',
    email: 'bob@example.com',
    thumbnail: '',
    availabilityStatus: 'offline' as const
  }
];

export const mockTeams = [
  { id: 1, name: 'Sales' },
  { id: 2, name: 'Support' },
  { id: 3, name: 'Management' },
  { id: 4, name: 'Administration' }
];

// Generate mock heatmap data (24 hours × 7 days)
export function generateMockHeatmapData(days: number = 7): HeatmapData[] {
  const data: HeatmapData[] = [];
  const now = new Date();
  
  for (let day = 0; day < days; day++) {
    const date = new Date(now);
    date.setDate(date.getDate() - day);
    date.setHours(0, 0, 0, 0);
    
    for (let hour = 0; hour < 24; hour++) {
      const timestamp = Math.floor(date.getTime() / 1000) + (hour * 3600);
      const value = Math.floor(Math.random() * 50) + 1; // Random value 1-50
      
      data.push({ timestamp, value });
    }
  }
  
  return data.sort((a, b) => a.timestamp - b.timestamp);
}