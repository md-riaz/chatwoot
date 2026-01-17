/**
 * Layout component types
 */

export interface NavigationItem {
  id: string;
  label: string;
  icon?: string;
  href: string;
  badge?: number;
  activeOn?: string[];
  children?: NavigationItem[];
}

export interface UserMenuItem {
  id: string;
  label: string;
  icon?: string;
  href?: string;
  onClick?: () => void;
  divider?: boolean;
}

export interface AccountInfo {
  id: number;
  name: string;
  logo?: string;
  role: string;
}

export interface NotificationItem {
  id: number;
  title: string;
  message: string;
  createdAt: string;
  read: boolean;
  type: 'info' | 'success' | 'warning' | 'error';
  href?: string;
}

export interface SidebarSection {
  id: string;
  title?: string;
  items: NavigationItem[];
  collapsible?: boolean;
  collapsed?: boolean;
}
