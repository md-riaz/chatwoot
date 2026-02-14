/**
 * Presence Store using Svelte 5 runes
 * Manages user presence, typing indicators, and real-time status
 */

interface PresenceUser {
  id: number;
  name: string;
  avatarUrl: string;
  type: 'agent' | 'contact';
  status: 'online' | 'offline' | 'away' | 'busy';
  metadata?: any;
  lastSeen?: string;
}

interface TypingUser {
  id: number;
  name: string;
  type: 'agent' | 'contact';
}

interface PresenceState {
  users: Map<number, PresenceUser>;
  typingUsers: Map<number, Set<number>>; // conversationId -> Set of user IDs
  members: Map<string, any>; // Pusher presence members
}

class PresenceStore {
  private state = $state<PresenceState>({
    users: new Map(),
    typingUsers: new Map(),
    members: new Map(),
  });

  // Reactive getters
  get users() {
    return Array.from(this.state.users.values());
  }

  get onlineUsers() {
    return Array.from(this.state.users.values()).filter(
      user => user.status === 'online'
    );
  }

  get offlineUsers() {
    return Array.from(this.state.users.values()).filter(
      user => user.status === 'offline'
    );
  }

  get awayUsers() {
    return Array.from(this.state.users.values()).filter(
      user => user.status === 'away'
    );
  }

  get busyUsers() {
    return Array.from(this.state.users.values()).filter(
      user => user.status === 'busy'
    );
  }

  get totalOnlineCount() {
    return this.onlineUsers.length;
  }

  /**
   * Get typing users for a specific conversation
   */
  getTypingUsers(conversationId: number): number[] {
    const typingSet = this.state.typingUsers.get(conversationId);
    return typingSet ? Array.from(typingSet) : [];
  }

  /**
   * Get typing user details for a conversation
   */
  getTypingUserDetails(conversationId: number): PresenceUser[] {
    const typingUserIds = this.getTypingUsers(conversationId);
    return typingUserIds
      .map(id => this.state.users.get(id))
      .filter((user): user is PresenceUser => user !== undefined);
  }

  /**
   * Check if any users are typing in a conversation
   */
  isAnyoneTyping(conversationId: number): boolean {
    const typingSet = this.state.typingUsers.get(conversationId);
    return typingSet ? typingSet.size > 0 : false;
  }

  /**
   * Check if a specific user is typing in a conversation
   */
  isUserTyping(conversationId: number, userId: number): boolean {
    const typingSet = this.state.typingUsers.get(conversationId);
    return typingSet ? typingSet.has(userId) : false;
  }

  /**
   * Get user presence information
   */
  getUserPresence(userId: number): PresenceUser | undefined {
    return this.state.users.get(userId);
  }

  /**
   * Check if user is online
   */
  isUserOnline(userId: number): boolean {
    const user = this.state.users.get(userId);
    return user?.status === 'online';
  }

  /**
   * Check if user is away
   */
  isUserAway(userId: number): boolean {
    const user = this.state.users.get(userId);
    return user?.status === 'away';
  }

  /**
   * Update user presence status
   */
  updateUserPresence(user: any, status: string, metadata?: any): void {
    const presenceUser: PresenceUser = {
      id: user.id,
      name: user.name,
      avatarUrl: user.avatar_url || user.avatarUrl || '',
      type: user.type || 'agent',
      status: status as 'online' | 'offline' | 'away' | 'busy',
      metadata,
      lastSeen: new Date().toISOString(),
    };

    this.state.users.set(user.id, presenceUser);
  }

  /**
   * Set typing status for a user in a conversation
   */
  setTyping(
    conversationId: number,
    typer: TypingUser,
    isTyping: boolean
  ): void {
    if (!this.state.typingUsers.has(conversationId)) {
      this.state.typingUsers.set(conversationId, new Set());
    }

    const typingSet = this.state.typingUsers.get(conversationId)!;

    if (isTyping) {
      typingSet.add(typer.id);

      // Auto-clear typing after 10 seconds (fallback)
      setTimeout(() => {
        this.setTyping(conversationId, typer, false);
      }, 10000);
    } else {
      typingSet.delete(typer.id);
    }

    // Clean up empty sets
    if (typingSet.size === 0) {
      this.state.typingUsers.delete(conversationId);
    }
  }

  /**
   * Clear all typing indicators for a conversation
   */
  clearTyping(conversationId: number): void {
    this.state.typingUsers.delete(conversationId);
  }

  /**
   * Clear typing for a specific user across all conversations
   */
  clearUserTyping(userId: number): void {
    this.state.typingUsers.forEach((typingSet, conversationId) => {
      if (typingSet.has(userId)) {
        typingSet.delete(userId);
        if (typingSet.size === 0) {
          this.state.typingUsers.delete(conversationId);
        }
      }
    });
  }

  /**
   * Add presence member (from Pusher presence channel)
   */
  addMember(member: any): void {
    this.state.members.set(member.id, member);

    // Also update user presence
    this.updateUserPresence(member, 'online');
  }

  /**
   * Remove presence member (from Pusher presence channel)
   */
  removeMember(member: any): void {
    this.state.members.delete(member.id);

    // Update user status to offline
    const user = this.state.users.get(member.id);
    if (user) {
      this.updateUserPresence(user, 'offline');
    }
  }

  /**
   * Get all presence members
   */
  get members() {
    return Array.from(this.state.members.values());
  }

  /**
   * Get member count
   */
  get memberCount() {
    return this.state.members.size;
  }

  /**
   * Set user as away (inactive)
   */
  setUserAway(userId: number): void {
    const user = this.state.users.get(userId);
    if (user) {
      this.updateUserPresence(user, 'away');
    }
  }

  /**
   * Set user as online (active)
   */
  setUserOnline(userId: number): void {
    const user = this.state.users.get(userId);
    if (user) {
      this.updateUserPresence(user, 'online');
    }
  }

  /**
   * Set user as offline
   */
  setUserOffline(userId: number): void {
    const user = this.state.users.get(userId);
    if (user) {
      this.updateUserPresence(user, 'offline');
    }
  }

  /**
   * Bulk update user statuses
   */
  updateMultipleUserPresence(
    updates: Array<{ userId: number; status: string; metadata?: any }>
  ): void {
    updates.forEach(({ userId, status, metadata }) => {
      const user = this.state.users.get(userId);
      if (user) {
        this.updateUserPresence(user, status, metadata);
      }
    });
  }

  /**
   * Get users by status
   */
  getUsersByStatus(
    status: 'online' | 'offline' | 'away' | 'busy'
  ): PresenceUser[] {
    return Array.from(this.state.users.values()).filter(
      user => user.status === status
    );
  }

  /**
   * Get typing summary for display
   */
  getTypingSummary(conversationId: number): string {
    const typingUsers = this.getTypingUserDetails(conversationId);

    if (typingUsers.length === 0) {
      return '';
    }

    if (typingUsers.length === 1) {
      return `${typingUsers[0].name} is typing...`;
    }

    if (typingUsers.length === 2) {
      return `${typingUsers[0].name} and ${typingUsers[1].name} are typing...`;
    }

    return `${typingUsers[0].name} and ${typingUsers.length - 1} others are typing...`;
  }

  /**
   * Reset all presence data
   */
  reset(): void {
    this.state.users.clear();
    this.state.typingUsers.clear();
    this.state.members.clear();
  }

  /**
   * Remove user from all presence data
   */
  removeUser(userId: number): void {
    this.state.users.delete(userId);
    this.clearUserTyping(userId);
    this.state.members.delete(userId.toString());
  }

  /**
   * Get presence statistics
   */
  get stats() {
    return {
      totalUsers: this.state.users.size,
      onlineUsers: this.onlineUsers.length,
      offlineUsers: this.offlineUsers.length,
      awayUsers: this.awayUsers.length,
      busyUsers: this.busyUsers.length,
      totalMembers: this.state.members.size,
      activeConversations: this.state.typingUsers.size,
    };
  }
}

// Export singleton instance
export const presenceStore = new PresenceStore();
