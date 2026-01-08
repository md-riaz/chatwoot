/**
 * Color Utilities
 * Functions for color manipulation and conversion
 */

/**
 * Convert hex color to RGB
 * @example hexToRgb('#ff5733') // { r: 255, g: 87, b: 51 }
 */
export function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
  // Remove # if present
  hex = hex.replace(/^#/, '');

  // Parse 3-digit hex
  if (hex.length === 3) {
    hex = hex
      .split('')
      .map(char => char + char)
      .join('');
  }

  if (hex.length !== 6) return null;

  const num = parseInt(hex, 16);
  const r = (num >> 16) & 255;
  const g = (num >> 8) & 255;
  const b = num & 255;

  return { r, g, b };
}

/**
 * Convert RGB to hex
 * @example rgbToHex(255, 87, 51) // '#ff5733'
 */
export function rgbToHex(r: number, g: number, b: number): string {
  const toHex = (n: number) => {
    const hex = Math.max(0, Math.min(255, Math.round(n))).toString(16);
    return hex.length === 1 ? '0' + hex : hex;
  };

  return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
}

/**
 * Convert hex color to HSL
 */
export function hexToHsl(hex: string): { h: number; s: number; l: number } | null {
  const rgb = hexToRgb(hex);
  if (!rgb) return null;

  return rgbToHsl(rgb.r, rgb.g, rgb.b);
}

/**
 * Convert RGB to HSL
 */
export function rgbToHsl(r: number, g: number, b: number): { h: number; s: number; l: number } {
  r /= 255;
  g /= 255;
  b /= 255;

  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  let h = 0;
  let s = 0;
  const l = (max + min) / 2;

  if (max !== min) {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

    switch (max) {
      case r:
        h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
        break;
      case g:
        h = ((b - r) / d + 2) / 6;
        break;
      case b:
        h = ((r - g) / d + 4) / 6;
        break;
    }
  }

  return {
    h: Math.round(h * 360),
    s: Math.round(s * 100),
    l: Math.round(l * 100),
  };
}

/**
 * Lighten a color by a percentage
 * @param hex - Hex color code
 * @param percent - Percentage to lighten (0-100)
 */
export function lighten(hex: string, percent: number): string {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;

  const { r, g, b } = rgb;
  const factor = 1 + percent / 100;

  return rgbToHex(
    Math.min(255, r * factor),
    Math.min(255, g * factor),
    Math.min(255, b * factor)
  );
}

/**
 * Darken a color by a percentage
 * @param hex - Hex color code
 * @param percent - Percentage to darken (0-100)
 */
export function darken(hex: string, percent: number): string {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;

  const { r, g, b } = rgb;
  const factor = 1 - percent / 100;

  return rgbToHex(r * factor, g * factor, b * factor);
}

/**
 * Adjust brightness of a color
 * @param hex - Hex color code
 * @param amount - Amount to adjust (-255 to 255)
 */
export function adjustBrightness(hex: string, amount: number): string {
  const rgb = hexToRgb(hex);
  if (!rgb) return hex;

  return rgbToHex(rgb.r + amount, rgb.g + amount, rgb.b + amount);
}

/**
 * Calculate contrast ratio between two colors
 * @returns Contrast ratio (1-21)
 */
export function getContrastRatio(hex1: string, hex2: string): number {
  const getLuminance = (hex: string): number => {
    const rgb = hexToRgb(hex);
    if (!rgb) return 0;

    const { r, g, b } = rgb;
    const [rs, gs, bs] = [r, g, b].map(c => {
      c /= 255;
      return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });

    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
  };

  const lum1 = getLuminance(hex1);
  const lum2 = getLuminance(hex2);

  const brightest = Math.max(lum1, lum2);
  const darkest = Math.min(lum1, lum2);

  return (brightest + 0.05) / (darkest + 0.05);
}

/**
 * Check if text color should be light or dark based on background
 * @returns 'light' or 'dark'
 */
export function getContrastTextColor(bgHex: string): 'light' | 'dark' {
  const rgb = hexToRgb(bgHex);
  if (!rgb) return 'dark';

  // Calculate perceived brightness
  const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;

  return brightness > 128 ? 'dark' : 'light';
}

/**
 * Check if color meets WCAG AA accessibility standards
 * @param textColor - Text color hex
 * @param bgColor - Background color hex
 * @param isLargeText - Whether text is large (>=18pt or >=14pt bold)
 */
export function meetsWCAGAA(
  textColor: string,
  bgColor: string,
  isLargeText: boolean = false
): boolean {
  const ratio = getContrastRatio(textColor, bgColor);
  return isLargeText ? ratio >= 3 : ratio >= 4.5;
}

/**
 * Check if color meets WCAG AAA accessibility standards
 */
export function meetsWCAGAAA(
  textColor: string,
  bgColor: string,
  isLargeText: boolean = false
): boolean {
  const ratio = getContrastRatio(textColor, bgColor);
  return isLargeText ? ratio >= 4.5 : ratio >= 7;
}

/**
 * Generate random hex color
 */
export function randomColor(): string {
  return `#${Math.floor(Math.random() * 16777215)
    .toString(16)
    .padStart(6, '0')}`;
}

/**
 * Blend two colors
 * @param color1 - First hex color
 * @param color2 - Second hex color
 * @param weight - Weight of first color (0-1)
 */
export function blendColors(color1: string, color2: string, weight: number = 0.5): string {
  const rgb1 = hexToRgb(color1);
  const rgb2 = hexToRgb(color2);

  if (!rgb1 || !rgb2) return color1;

  const r = Math.round(rgb1.r * weight + rgb2.r * (1 - weight));
  const g = Math.round(rgb1.g * weight + rgb2.g * (1 - weight));
  const b = Math.round(rgb1.b * weight + rgb2.b * (1 - weight));

  return rgbToHex(r, g, b);
}

/**
 * Get color palette from base color
 * Generates shades from lightest to darkest
 */
export function getColorPalette(baseColor: string, steps: number = 9): string[] {
  const palette: string[] = [];
  const step = 100 / (steps - 1);

  for (let i = 0; i < steps; i++) {
    const amount = step * i - 50; // -50 to +50
    if (amount < 0) {
      palette.push(lighten(baseColor, Math.abs(amount)));
    } else if (amount > 0) {
      palette.push(darken(baseColor, amount));
    } else {
      palette.push(baseColor);
    }
  }

  return palette;
}

/**
 * Check if color is valid hex
 */
export function isValidHex(color: string): boolean {
  return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color);
}
