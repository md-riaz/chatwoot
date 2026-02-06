/**
 * Heatmap Utility Functions
 * Data processing and color calculation for heatmap visualization
 * Matches Vue ReportsDataHelper functionality
 */

import type { HeatmapData } from '$lib/stores/reports.svelte';

export interface HeatmapRow {
  dateKey: string;
  data: HeatmapData[];
  dataHash: string;
}

/**
 * Group heatmap data by day
 * Creates a map of date strings to hourly data arrays
 */
export function groupHeatmapByDay(heatmapData: HeatmapData[]): Map<string, HeatmapData[]> {
  const grouped = new Map<string, HeatmapData[]>();
  
  heatmapData.forEach(item => {
    const date = new Date(item.timestamp * 1000);
    const dateKey = date.toISOString().split('T')[0]; // YYYY-MM-DD format
    
    if (!grouped.has(dateKey)) {
      grouped.set(dateKey, []);
    }
    
    grouped.get(dateKey)!.push(item);
  });
  
  // Sort each day's data by hour
  grouped.forEach(dayData => {
    dayData.sort((a, b) => a.timestamp - b.timestamp);
  });
  
  return grouped;
}

/**
 * Calculate quantile intervals for color intensity
 * Matches Vue getQuantileIntervals functionality
 */
export function getQuantileIntervals(values: number[], quantiles: number[]): number[] {
  if (values.length === 0) return [];
  
  const sortedValues = [...values].sort((a, b) => a - b);
  const intervals: number[] = [];
  
  quantiles.forEach(q => {
    const index = Math.ceil(q * sortedValues.length) - 1;
    const clampedIndex = Math.max(0, Math.min(index, sortedValues.length - 1));
    intervals.push(sortedValues[clampedIndex]);
  });
  
  return intervals;
}

/**
 * Color schemes for heatmap visualization
 * Matches Vue COLOR_SCHEMES
 */
export const COLOR_SCHEMES = {
  blue: [
    'bg-blue-100 border border-blue-200/30 dark:bg-blue-900/20 dark:border-blue-800/30',
    'bg-blue-200 border border-blue-300/30 dark:bg-blue-800/30 dark:border-blue-700/30',
    'bg-blue-400 border border-blue-500/30 dark:bg-blue-700/50 dark:border-blue-600/30',
    'bg-blue-500 border border-blue-600/30 dark:bg-blue-600/60 dark:border-blue-500/30',
    'bg-blue-700 border border-blue-800/30 dark:bg-blue-500/70 dark:border-blue-400/30',
    'bg-blue-800 border border-blue-900/30 dark:bg-blue-400/80 dark:border-blue-300/30',
  ],
  green: [
    'bg-teal-100 border border-teal-200/30 dark:bg-teal-900/20 dark:border-teal-800/30',
    'bg-teal-200 border border-teal-300/30 dark:bg-teal-800/30 dark:border-teal-700/30',
    'bg-teal-400 border border-teal-500/30 dark:bg-teal-700/50 dark:border-teal-600/30',
    'bg-teal-500 border border-teal-600/30 dark:bg-teal-600/60 dark:border-teal-500/30',
    'bg-teal-700 border border-teal-800/30 dark:bg-teal-500/70 dark:border-teal-400/30',
    'bg-teal-800 border border-teal-900/30 dark:bg-teal-400/80 dark:border-teal-300/30',
  ]
};

/**
 * Get CSS class for heatmap cell based on value and quantile ranges
 * Matches Vue getHeatmapLevelClass functionality
 */
export function getHeatmapLevelClass(
  value: number,
  quantileRanges: number[],
  colorScheme: 'blue' | 'green' = 'blue'
): string {
  if (!value) {
    return 'border border-slate-200 bg-slate-50 dark:bg-slate-800/30 dark:border-slate-700';
  }
  
  let level = [...quantileRanges, Infinity].findIndex(
    range => value <= range && value > 0
  );
  
  if (level > 5) level = 5;
  
  if (level === 0) {
    return 'border border-slate-200 bg-slate-50 dark:bg-slate-800/30 dark:border-slate-700';
  }
  
  return COLOR_SCHEMES[colorScheme][level - 1];
}

/**
 * Format date for heatmap display
 */
export function formatHeatmapDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
}

/**
 * Get day of week for heatmap row labels
 */
export function getDayOfWeek(dateString: string): string {
  const date = new Date(dateString);
  const days = [
    'Sunday',
    'Monday', 
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
  ];
  return days[date.getDay()];
}

/**
 * Generate 24-hour time slots for heatmap columns
 */
export function generateTimeSlots(): string[] {
  return Array.from({ length: 24 }, (_, i) => i.toString());
}

/**
 * Ensure heatmap data has all 24 hours for each day
 * Fills missing hours with 0 values
 */
export function fillMissingHours(dayData: HeatmapData[], dateKey: string): HeatmapData[] {
  const baseDate = new Date(dateKey + 'T00:00:00');
  const filledData: HeatmapData[] = [];
  
  for (let hour = 0; hour < 24; hour++) {
    const hourTimestamp = Math.floor(baseDate.getTime() / 1000) + (hour * 3600);
    const existingData = dayData.find(d => {
      const dataHour = new Date(d.timestamp * 1000).getHours();
      return dataHour === hour;
    });
    
    filledData.push(existingData || { timestamp: hourTimestamp, value: 0 });
  }
  
  return filledData;
}