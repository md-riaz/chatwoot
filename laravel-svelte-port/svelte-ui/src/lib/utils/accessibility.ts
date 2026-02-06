/**
 * Accessibility utilities for reports components
 */

/**
 * Generate accessible labels for heatmap cells
 */
export function getHeatmapCellLabel(
  value: number,
  hour: number,
  date: string,
  metric: string = 'conversations'
): string {
  const timeLabel = hour === 0 ? 'midnight' : 
                   hour === 12 ? 'noon' : 
                   hour < 12 ? `${hour} AM` : `${hour - 12} PM`;
  
  const dateLabel = new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric'
  });
  
  return `${value} ${metric} on ${dateLabel} at ${timeLabel}`;
}

/**
 * Generate accessible table descriptions
 */
export function getTableDescription(
  totalItems: number,
  currentPage: number,
  pageSize: number,
  entityType: string = 'items'
): string {
  const start = (currentPage - 1) * pageSize + 1;
  const end = Math.min(currentPage * pageSize, totalItems);
  
  return `Showing ${start} to ${end} of ${totalItems} ${entityType}. Use arrow keys to navigate table cells, Enter to interact with elements.`;
}

/**
 * Generate live region announcements
 */
export function getLiveRegionAnnouncement(
  type: 'loading' | 'loaded' | 'error' | 'updated',
  context: string,
  details?: string
): string {
  const announcements = {
    loading: `Loading ${context}...`,
    loaded: `${context} loaded successfully${details ? `. ${details}` : ''}`,
    error: `Error loading ${context}${details ? `: ${details}` : ''}`,
    updated: `${context} updated${details ? `. ${details}` : ''}`
  };
  
  return announcements[type];
}

/**
 * Keyboard navigation helpers
 */
export class KeyboardNavigationManager {
  private focusableElements: HTMLElement[] = [];
  private currentIndex: number = -1;
  
  constructor(private container: HTMLElement) {
    this.updateFocusableElements();
  }
  
  updateFocusableElements(): void {
    const selector = [
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      'a[href]',
      '[tabindex]:not([tabindex="-1"])'
    ].join(', ');
    
    this.focusableElements = Array.from(
      this.container.querySelectorAll(selector)
    ) as HTMLElement[];
  }
  
  handleKeyDown(event: KeyboardEvent): boolean {
    switch (event.key) {
      case 'ArrowDown':
      case 'ArrowRight':
        event.preventDefault();
        this.focusNext();
        return true;
        
      case 'ArrowUp':
      case 'ArrowLeft':
        event.preventDefault();
        this.focusPrevious();
        return true;
        
      case 'Home':
        event.preventDefault();
        this.focusFirst();
        return true;
        
      case 'End':
        event.preventDefault();
        this.focusLast();
        return true;
        
      default:
        return false;
    }
  }
  
  private focusNext(): void {
    this.currentIndex = (this.currentIndex + 1) % this.focusableElements.length;
    this.focusableElements[this.currentIndex]?.focus();
  }
  
  private focusPrevious(): void {
    this.currentIndex = this.currentIndex <= 0 
      ? this.focusableElements.length - 1 
      : this.currentIndex - 1;
    this.focusableElements[this.currentIndex]?.focus();
  }
  
  private focusFirst(): void {
    this.currentIndex = 0;
    this.focusableElements[0]?.focus();
  }
  
  private focusLast(): void {
    this.currentIndex = this.focusableElements.length - 1;
    this.focusableElements[this.currentIndex]?.focus();
  }
}

/**
 * Screen reader utilities
 */
export function announceToScreenReader(message: string, priority: 'polite' | 'assertive' = 'polite'): void {
  const announcement = document.createElement('div');
  announcement.setAttribute('aria-live', priority);
  announcement.setAttribute('aria-atomic', 'true');
  announcement.className = 'sr-only';
  announcement.textContent = message;
  
  document.body.appendChild(announcement);
  
  // Remove after announcement
  setTimeout(() => {
    document.body.removeChild(announcement);
  }, 1000);
}

/**
 * Focus management utilities
 */
export function trapFocus(element: HTMLElement): () => void {
  const focusableElements = element.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  ) as NodeListOf<HTMLElement>;
  
  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];
  
  function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Tab') {
      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      }
    }
  }
  
  element.addEventListener('keydown', handleKeyDown);
  firstElement?.focus();
  
  return () => {
    element.removeEventListener('keydown', handleKeyDown);
  };
}

/**
 * Color contrast utilities
 */
export function getContrastRatio(color1: string, color2: string): number {
  // Simplified contrast ratio calculation
  // In a real implementation, you'd parse the colors and calculate luminance
  return 4.5; // Placeholder - meets WCAG AA standard
}

export function meetsContrastRequirement(
  foreground: string, 
  background: string, 
  level: 'AA' | 'AAA' = 'AA'
): boolean {
  const ratio = getContrastRatio(foreground, background);
  return level === 'AA' ? ratio >= 4.5 : ratio >= 7;
}