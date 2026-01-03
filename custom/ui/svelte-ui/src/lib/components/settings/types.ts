/**
 * Settings component types
 */

export interface SettingsSection {
  id: string;
  title: string;
  description?: string;
  icon?: string;
  href: string;
}

export interface SettingsNavItem {
  id: string;
  label: string;
  icon?: string;
  href: string;
  badge?: string;
}
