/**
 * Widget Config Store
 * 
 * Manages widget configuration and UI state.
 */

import type { WidgetConfig } from '../api/types';

class WidgetConfigStore {
  private config = $state<WidgetConfig | null>(null);
  private isOpen = $state(false);
  private isMinimized = $state(false);
  private unreadCount = $state(0);

  // Getters
  get configuration() {
    return this.config;
  }

  get open() {
    return this.isOpen;
  }

  get minimized() {
    return this.isMinimized;
  }

  get unread() {
    return this.unreadCount;
  }

  // Derived values
  get websiteToken() {
    return (this.config?.websiteToken || '');
  }

  get locale() {
    return (this.config?.locale || 'en');
  }

  get position() {
    return (this.config?.position || 'right');
  }

  get widgetColor() {
    return (this.config?.widgetColor || '#1f93ff');
  }

  get preChatFormEnabled() {
    return (this.config?.preChatFormEnabled || false);
  }

  get businessName() {
    return (this.config?.businessName || 'Support Team');
  }

  // Actions
  setConfig(config: WidgetConfig) {
    this.config = config;
  }

  toggle() {
    this.isOpen = !this.isOpen;
    if (this.isOpen) {
      this.unreadCount = 0;
    }
  }

  open() {
    this.isOpen = true;
    this.unreadCount = 0;
  }

  close() {
    this.isOpen = false;
  }

  minimize() {
    this.isMinimized = true;
  }

  maximize() {
    this.isMinimized = false;
  }

  incrementUnread() {
    if (!this.isOpen) {
      this.unreadCount++;
    }
  }

  resetUnread() {
    this.unreadCount = 0;
  }

  setUnreadCount(count: number) {
    this.unreadCount = count;
  }
}

export const widgetConfigStore = new WidgetConfigStore();
