/**
 * Navigation component types
 */

export interface NavItem {
  id: string;
  label: string;
  icon?: string;
  href: string;
  badge?: number;
  isActive?: boolean;
  children?: NavItem[];
}

export interface NavSection {
  id: string;
  title?: string;
  items: NavItem[];
}

export interface FilterChip {
  id: string;
  label: string;
  count: number;
  isActive: boolean;
}
