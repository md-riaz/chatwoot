/**
 * Availability Helpers
 * 
 * Utilities for determining agent availability and business hours.
 */

import type { BusinessHours, Agent } from '../api/types';

/**
 * Check if business is currently available based on business hours
 */
export function isBusinessAvailable(businessHours: BusinessHours): boolean {
  if (!businessHours.enabled) {
    return true; // Always available if business hours not configured
  }

  const now = new Date();
  const dayOfWeek = now.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
  const schedule = businessHours.schedule[dayOfWeek];

  if (!schedule || !schedule.enabled) {
    return false;
  }

  // Parse time strings (format: "HH:MM")
  const currentTime = now.getHours() * 60 + now.getMinutes();
  const [fromHours, fromMinutes] = schedule.from.split(':').map(Number);
  const [toHours, toMinutes] = schedule.to.split(':').map(Number);
  const fromTime = fromHours * 60 + fromMinutes;
  const toTime = toHours * 60 + toMinutes;

  return currentTime >= fromTime && currentTime <= toTime;
}

/**
 * Get available agents (online or busy)
 */
export function getAvailableAgents(agents: Agent[]): Agent[] {
  return agents.filter(
    (agent) =>
      agent.availabilityStatus === 'online' || agent.availabilityStatus === 'busy'
  );
}

/**
 * Check if any agent is online
 */
export function isAnyAgentOnline(agents: Agent[]): boolean {
  return agents.some((agent) => agent.availabilityStatus === 'online');
}

/**
 * Get availability message
 */
export function getAvailabilityMessage(
  agents: Agent[],
  businessHours: BusinessHours,
  replyTime?: string
): string {
  const onlineAgents = getAvailableAgents(agents);

  if (onlineAgents.length > 0) {
    const agentNames = onlineAgents.map((a) => a.name).join(', ');
    if (onlineAgents.length === 1) {
      return `${agentNames} is available`;
    }
    return `${onlineAgents.length} agents available`;
  }

  if (businessHours.enabled && !isBusinessAvailable(businessHours)) {
    return 'We are currently offline';
  }

  if (replyTime) {
    return `We'll reply in ${replyTime}`;
  }

  return 'We'll get back to you soon';
}

/**
 * Get next available time based on business hours
 */
export function getNextAvailableTime(businessHours: BusinessHours): Date | null {
  if (!businessHours.enabled) {
    return null;
  }

  const now = new Date();
  const daysOfWeek = [
    'sunday',
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
  ];

  // Check next 7 days
  for (let i = 1; i <= 7; i++) {
    const nextDay = new Date(now);
    nextDay.setDate(now.getDate() + i);
    nextDay.setHours(0, 0, 0, 0);

    const dayOfWeek = daysOfWeek[nextDay.getDay()];
    const schedule = businessHours.schedule[dayOfWeek];

    if (schedule && schedule.enabled) {
      const [hours, minutes] = schedule.from.split(':').map(Number);
      nextDay.setHours(hours, minutes);
      return nextDay;
    }
  }

  return null;
}

/**
 * Format availability time
 */
export function formatAvailabilityTime(date: Date): string {
  const now = new Date();
  const tomorrow = new Date(now);
  tomorrow.setDate(now.getDate() + 1);
  tomorrow.setHours(0, 0, 0, 0);

  const targetDay = new Date(date);
  targetDay.setHours(0, 0, 0, 0);

  const time = date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  });

  if (targetDay.getTime() === new Date(now).setHours(0, 0, 0, 0)) {
    return `Today at ${time}`;
  }

  if (targetDay.getTime() === tomorrow.getTime()) {
    return `Tomorrow at ${time}`;
  }

  const day = date.toLocaleDateString('en-US', { weekday: 'long' });
  return `${day} at ${time}`;
}
