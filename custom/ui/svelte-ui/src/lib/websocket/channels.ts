/**
 * WebSocket Channels
 * Predefined channel helpers for common WebSocket subscriptions
 */

import type { MessageHandler, UnsubscribeFunction } from './types';
import { getWebSocketClient } from './client';

/**
 * Subscribe to conversation updates
 * Events: message.created, message.updated, conversation.created, etc.
 */
export function subscribeToConversations(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `conversations:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to notifications
 * Events: notification.created, notification.updated, notification.deleted
 */
export function subscribeToNotifications(
  accountId: number,
  userId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `notifications:${accountId}:${userId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to presence updates
 * Events: presence.update for agents and contacts
 */
export function subscribeToPresence(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `presence:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to typing indicators
 * Events: conversation.typing_on, conversation.typing_off
 */
export function subscribeToTyping(
  conversationId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `typing:${conversationId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to contact updates
 * Events: contact.created, contact.updated, contact.deleted
 */
export function subscribeToContacts(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `contacts:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to agent updates
 * Events: agent.updated, assignee.changed
 */
export function subscribeToAgents(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `agents:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to inbox updates
 * Events: inbox.created, inbox.updated, inbox.deleted
 */
export function subscribeToInboxes(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `inboxes:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to team updates
 * Events: team.created, team.updated, team.deleted
 */
export function subscribeToTeams(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `teams:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to label updates
 * Events: label.created, label.updated, label.deleted
 */
export function subscribeToLabels(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `labels:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Subscribe to cache invalidation events
 * Events: account.cache_invalidated
 */
export function subscribeToCacheInvalidation(
  accountId: number,
  callback: MessageHandler
): UnsubscribeFunction {
  const ws = getWebSocketClient();
  const channel = `cache:${accountId}`;
  return ws.subscribe(channel, callback);
}

/**
 * Send typing indicator
 */
export function sendTypingIndicator(
  conversationId: number,
  isTyping: boolean
): void {
  const ws = getWebSocketClient();
  const channel = `typing:${conversationId}`;
  ws.send(channel, {
    event: isTyping ? 'conversation.typing_on' : 'conversation.typing_off',
    conversationId,
  });
}

/**
 * Send message read acknowledgement
 */
export function sendMessageRead(conversationId: number, messageId: number): void {
  const ws = getWebSocketClient();
  const channel = `conversations`;
  ws.send(channel, {
    event: 'message.read',
    conversationId,
    messageId,
  });
}
