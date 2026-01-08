/**
 * Audio Notification Utilities
 * 
 * Utilities for playing notification sounds in the widget.
 */

let audioContext: AudioContext | null = null;
let notificationSound: AudioBuffer | null = null;
let enabled = true;

/**
 * Initialize audio notifications
 */
export async function initAudioNotifications(): Promise<void> {
  if (typeof window === 'undefined') return;

  try {
    // Create audio context
    const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
    audioContext = new AudioContextClass();

    // Load default notification sound (simple beep)
    // In production, this should load from an actual audio file
    const sampleRate = audioContext.sampleRate;
    const duration = 0.2; // 200ms
    const frequency = 800; // 800Hz
    const buffer = audioContext.createBuffer(1, sampleRate * duration, sampleRate);
    const channel = buffer.getChannelData(0);

    for (let i = 0; i < buffer.length; i++) {
      const t = i / sampleRate;
      channel[i] = Math.sin(2 * Math.PI * frequency * t) * Math.exp(-t * 5);
    }

    notificationSound = buffer;
  } catch (err) {
    console.error('Failed to initialize audio notifications:', err);
  }
}

/**
 * Load custom notification sound from URL
 */
export async function loadNotificationSound(url: string): Promise<void> {
  if (!audioContext) {
    await initAudioNotifications();
  }

  if (!audioContext) return;

  try {
    const response = await fetch(url);
    const arrayBuffer = await response.arrayBuffer();
    notificationSound = await audioContext.decodeAudioData(arrayBuffer);
  } catch (err) {
    console.error('Failed to load notification sound:', err);
  }
}

/**
 * Play notification sound
 */
export function playNotificationSound(): void {
  if (!enabled || !audioContext || !notificationSound) return;

  try {
    const source = audioContext.createBufferSource();
    source.buffer = notificationSound;
    source.connect(audioContext.destination);
    source.start(0);
  } catch (err) {
    console.error('Failed to play notification sound:', err);
  }
}

/**
 * Enable notification sounds
 */
export function enableNotificationSounds(): void {
  enabled = true;
}

/**
 * Disable notification sounds
 */
export function disableNotificationSounds(): void {
  enabled = false;
}

/**
 * Check if notification sounds are enabled
 */
export function areNotificationSoundsEnabled(): boolean {
  return enabled;
}

/**
 * Toggle notification sounds
 */
export function toggleNotificationSounds(): boolean {
  enabled = !enabled;
  return enabled;
}
