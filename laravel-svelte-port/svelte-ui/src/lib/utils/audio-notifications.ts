/**
 * Audio Notification Manager
 * Replaces Vue DashboardAudioNotificationHelper and widget audio notifications
 * Provides same functionality as Vue ActionCable audio system
 */

interface AudioNotificationConfig {
  enabled: boolean;
  volume: number;
  dashboardSounds: boolean;
  widgetSounds: boolean;
}

class AudioNotificationManager {
  private audioContext: AudioContext | null = null;
  private config: AudioNotificationConfig = {
    enabled: true,
    volume: 0.5,
    dashboardSounds: true,
    widgetSounds: true,
  };
  private soundBuffers = new Map<string, AudioBuffer>();
  private isInitialized = false;

  /**
   * Initialize audio context (must be called after user interaction)
   */
  async initialize(): Promise<void> {
    if (this.isInitialized) return;

    try {
      this.audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
      await this.loadSounds();
      this.isInitialized = true;
    } catch (error) {
      console.warn('Failed to initialize audio notifications:', error);
    }
  }

  /**
   * Load notification sounds
   */
  private async loadSounds(): Promise<void> {
    const sounds = {
      'new-message': '/sounds/notification.mp3',
      'widget-message': '/sounds/widget-notification.mp3',
      'mention': '/sounds/mention.mp3',
    };

    const loadPromises = Object.entries(sounds).map(async ([key, url]) => {
      try {
        const response = await fetch(url);
        if (response.ok) {
          const arrayBuffer = await response.arrayBuffer();
          const audioBuffer = await this.audioContext!.decodeAudioData(arrayBuffer);
          this.soundBuffers.set(key, audioBuffer);
        }
      } catch (error) {
        console.warn(`Failed to load sound ${key}:`, error);
      }
    });

    await Promise.all(loadPromises);
  }

  /**
   * Play notification sound
   */
  private async playSound(soundKey: string): Promise<void> {
    if (!this.isInitialized || !this.config.enabled || !this.audioContext) {
      return;
    }

    const buffer = this.soundBuffers.get(soundKey);
    if (!buffer) {
      console.warn(`Sound ${soundKey} not found`);
      return;
    }

    try {
      const source = this.audioContext.createBufferSource();
      const gainNode = this.audioContext.createGain();

      source.buffer = buffer;
      gainNode.gain.value = this.config.volume;

      source.connect(gainNode);
      gainNode.connect(this.audioContext.destination);

      source.start();
    } catch (error) {
      console.error('Failed to play notification sound:', error);
    }
  }

  /**
   * Handle new message notification (Dashboard)
   * Matches Vue DashboardAudioNotificationHelper.onNewMessage
   */
  async onNewMessage(data: any): Promise<void> {
    if (!this.config.dashboardSounds) return;

    // Initialize on first use (after user interaction)
    if (!this.isInitialized) {
      await this.initialize();
    }

    // Check if message is from current user (don't play sound for own messages)
    const currentUserId = this.getCurrentUserId();
    if (data.sender_id === currentUserId) {
      return;
    }

    // Check if conversation is muted
    if (data.conversation?.muted) {
      return;
    }

    // Play appropriate sound based on message type
    if (data.message_type === 'incoming') {
      await this.playSound('new-message');
    }
  }

  /**
   * Handle widget message notification
   * Matches Vue playNewMessageNotificationInWidget
   */
  async onWidgetMessage(): Promise<void> {
    if (!this.config.widgetSounds) return;

    if (!this.isInitialized) {
      await this.initialize();
    }

    await this.playSound('widget-message');
  }

  /**
   * Handle mention notification
   */
  async onMention(): Promise<void> {
    if (!this.config.dashboardSounds) return;

    if (!this.isInitialized) {
      await this.initialize();
    }

    await this.playSound('mention');
  }

  /**
   * Update configuration
   */
  updateConfig(config: Partial<AudioNotificationConfig>): void {
    this.config = { ...this.config, ...config };

    // Save to localStorage
    localStorage.setItem('audio_config', JSON.stringify(this.config));
  }

  /**
   * Load configuration from localStorage
   */
  loadConfig(): void {
    try {
      const saved = localStorage.getItem('audio_config');
      if (saved) {
        const config = JSON.parse(saved);
        this.config = { ...this.config, ...config };
      }
    } catch (error) {
      console.warn('Failed to load audio config:', error);
    }
  }

  /**
   * Get current configuration
   */
  getConfig(): AudioNotificationConfig {
    return { ...this.config };
  }

  /**
   * Enable/disable all notifications
   */
  setEnabled(enabled: boolean): void {
    this.updateConfig({ enabled });
  }

  /**
   * Set volume (0-1)
   */
  setVolume(volume: number): void {
    this.updateConfig({ volume: Math.max(0, Math.min(1, volume)) });
  }

  /**
   * Get current user ID (helper method)
   */
  private getCurrentUserId(): number | null {
    // This should be implemented based on your auth store
    // For now, return null to disable self-message filtering
    return null;
  }

  /**
   * Cleanup resources
   */
  cleanup(): void {
    if (this.audioContext) {
      this.audioContext.close();
      this.audioContext = null;
    }
    this.soundBuffers.clear();
    this.isInitialized = false;
  }
}

// Singleton instance
export const audioNotificationManager = new AudioNotificationManager();

// Load config on initialization
audioNotificationManager.loadConfig();

// Widget-specific helper functions (matching Vue implementation)
export const playNewMessageNotificationInWidget = () => {
  audioNotificationManager.onWidgetMessage();
};

export const playMentionNotification = () => {
  audioNotificationManager.onMention();
};