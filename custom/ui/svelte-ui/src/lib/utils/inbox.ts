/**
 * Inbox icon utilities
 * Maps inbox channel types to icon names
 */

export function getInboxIconByType(
  channelType: string,
  medium?: string
): string {
  const iconMap: Record<string, string> = {
    'Channel::WebWidget': '💬',
    'Channel::FacebookPage': '📘',
    'Channel::TwitterProfile': '🐦',
    'Channel::TwilioSms': '📱',
    'Channel::Whatsapp': '📞',
    'Channel::Email': '📧',
    'Channel::Api': '🔌',
    'Channel::Line': '📲',
    'Channel::Telegram': '✈️',
    'Channel::Sms': '💬',
  };
  
  return iconMap[channelType] || '📥';
}
