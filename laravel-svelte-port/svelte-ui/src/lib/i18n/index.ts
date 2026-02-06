/**
 * Internationalization (i18n) configuration using svelte-i18n
 * Replaces vue-i18n from Vue application
 */

import { register, init, getLocaleFromNavigator, locale } from 'svelte-i18n';

/**
 * Supported locales
 * Based on Vue app's available translations
 */
export const SUPPORTED_LOCALES = [
  'am', 'ar', 'az', 'bg', 'bn', 'ca', 'cs', 'da', 'de', 'el',
  'en', 'es', 'et', 'eu', 'fa', 'fi', 'fr', 'he', 'hi', 'hr',
  'hu', 'id', 'it', 'ja', 'ka', 'kk', 'ko', 'lt', 'lv', 'ml',
  'mr', 'ms', 'nb', 'ne', 'nl', 'no', 'pl', 'pt', 'pt_BR', 'ro',
  'ru', 'sk', 'sl', 'sr', 'sv', 'sw', 'ta', 'te', 'th', 'tr',
  'uk', 'ur', 'uz', 'vi', 'zh_CN', 'zh_TW'
] as const;

export type SupportedLocale = typeof SUPPORTED_LOCALES[number];

/**
 * Default locale
 */
export const DEFAULT_LOCALE: SupportedLocale = 'en';

/**
 * RTL (Right-to-Left) languages
 */
export const RTL_LOCALES: SupportedLocale[] = ['ar', 'he', 'fa', 'ur'];

/**
 * Check if locale is RTL
 */
export function isRTL(localeCode: string): boolean {
  return RTL_LOCALES.includes(localeCode as SupportedLocale);
}

/**
 * Register all available locales for lazy loading
 */
function registerLocales() {
  SUPPORTED_LOCALES.forEach((localeCode) => {
    register(localeCode, () => import(`./locales/${localeCode}/index.json`));
  });
}

/**
 * Get preferred locale from storage or browser
 */
function getPreferredLocale(): SupportedLocale {
  // Check localStorage
  if (typeof localStorage !== 'undefined') {
    const stored = localStorage.getItem('chatwoot_locale');
    if (stored && SUPPORTED_LOCALES.includes(stored as SupportedLocale)) {
      return stored as SupportedLocale;
    }
  }
  
  // Check browser locale
  const browserLocale = getLocaleFromNavigator();
  if (browserLocale) {
    // Try exact match
    if (SUPPORTED_LOCALES.includes(browserLocale as SupportedLocale)) {
      return browserLocale as SupportedLocale;
    }
    
    // Try language code only (e.g., 'en' from 'en-US')
    const langCode = browserLocale.split('-')[0];
    if (SUPPORTED_LOCALES.includes(langCode as SupportedLocale)) {
      return langCode as SupportedLocale;
    }
  }
  
  return DEFAULT_LOCALE;
}

/**
 * Initialize i18n
 * Call this in root +layout.svelte
 */
export async function initI18n() {
  // Register locales for lazy loading and switch to the preferred locale.
  // The module-level `init()` already set a safe default initial locale so
  // components can call the translator synchronously without throwing.
  registerLocales();

  const preferredLocale = getPreferredLocale();

  // Set the preferred locale (this triggers loading of that locale)
  locale.set(preferredLocale);

  return preferredLocale;
}

/**
 * Switch to a different locale
 */
export async function switchLocale(newLocale: SupportedLocale): Promise<void> {
  if (!SUPPORTED_LOCALES.includes(newLocale)) {
    console.warn(`Locale '${newLocale}' is not supported`);
    return;
  }
  
  // Set locale (triggers reactive updates)
  locale.set(newLocale);
  
  // Persist to localStorage
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('chatwoot_locale', newLocale);
  }
  
  // Update document direction for RTL
  if (typeof document !== 'undefined') {
    document.documentElement.dir = isRTL(newLocale) ? 'rtl' : 'ltr';
    document.documentElement.lang = newLocale;
  }
}

/**
 * Get current locale
 */
export function getCurrentLocale(): string {
  let currentLocale = DEFAULT_LOCALE;
  
  const unsubscribe = locale.subscribe(value => {
    if (value) currentLocale = value as typeof DEFAULT_LOCALE;
  });
  unsubscribe();
  
  return currentLocale;
}

/**
 * Get locale display name
 */
export function getLocaleDisplayName(localeCode: SupportedLocale): string {
  const names: Record<string, string> = {
    'am': 'አማርኛ (Amharic)',
    'ar': 'العربية (Arabic)',
    'az': 'Azərbaycan (Azerbaijani)',
    'bg': 'Български (Bulgarian)',
    'bn': 'বাংলা (Bengali)',
    'ca': 'Català (Catalan)',
    'cs': 'Čeština (Czech)',
    'da': 'Dansk (Danish)',
    'de': 'Deutsch (German)',
    'el': 'Ελληνικά (Greek)',
    'en': 'English',
    'es': 'Español (Spanish)',
    'et': 'Eesti (Estonian)',
    'eu': 'Euskara (Basque)',
    'fa': 'فارسی (Persian)',
    'fi': 'Suomi (Finnish)',
    'fr': 'Français (French)',
    'he': 'עברית (Hebrew)',
    'hi': 'हिन्दी (Hindi)',
    'hr': 'Hrvatski (Croatian)',
    'hu': 'Magyar (Hungarian)',
    'id': 'Bahasa Indonesia (Indonesian)',
    'it': 'Italiano (Italian)',
    'ja': '日本語 (Japanese)',
    'ka': 'ქართული (Georgian)',
    'kk': 'Қазақша (Kazakh)',
    'ko': '한국어 (Korean)',
    'lt': 'Lietuvių (Lithuanian)',
    'lv': 'Latviešu (Latvian)',
    'ml': 'മലയാളം (Malayalam)',
    'mr': 'मराठी (Marathi)',
    'ms': 'Bahasa Melayu (Malay)',
    'nb': 'Norsk Bokmål (Norwegian)',
    'ne': 'नेपाली (Nepali)',
    'nl': 'Nederlands (Dutch)',
    'no': 'Norsk (Norwegian)',
    'pl': 'Polski (Polish)',
    'pt': 'Português (Portuguese)',
    'pt_BR': 'Português Brasileiro (Brazilian Portuguese)',
    'ro': 'Română (Romanian)',
    'ru': 'Русский (Russian)',
    'sk': 'Slovenčina (Slovak)',
    'sl': 'Slovenščina (Slovenian)',
    'sr': 'Српски (Serbian)',
    'sv': 'Svenska (Swedish)',
    'sw': 'Kiswahili (Swahili)',
    'ta': 'தமிழ் (Tamil)',
    'te': 'తెలుగు (Telugu)',
    'th': 'ไทย (Thai)',
    'tr': 'Türkçe (Turkish)',
    'uk': 'Українська (Ukrainian)',
    'ur': 'اردو (Urdu)',
    'uz': 'Oʻzbekcha (Uzbek)',
    'vi': 'Tiếng Việt (Vietnamese)',
    'zh_CN': '简体中文 (Simplified Chinese)',
    'zh_TW': '繁體中文 (Traditional Chinese)'
  };
  
  return names[localeCode] || localeCode;
}

/**
 * Get all available locales with display names
 */
export function getAvailableLocales(): Array<{ code: SupportedLocale; name: string }> {
  return SUPPORTED_LOCALES.map(code => ({
    code,
    name: getLocaleDisplayName(code)
  }));
}

// Re-export svelte-i18n utilities
export { locale, _, t, date, time, number, isLoading, dictionary } from 'svelte-i18n';

// Ensure a safe default initial locale is set at module load so the `_` translator
// is available synchronously during early renders. Later `initI18n()` will
// register locales and switch to the user's preferred locale.
// NOTE: calling `init` here with `initialLocale` set to `DEFAULT_LOCALE` avoids
// "Cannot format a message without first setting the initial locale" errors
// when components call `$_('...')` during render.
// Register loaders for all supported locales so that the initial locale
// can be loaded when `init` runs. This prevents missing-key warnings when
// components call the translator during early render.
registerLocales();

init({
  fallbackLocale: DEFAULT_LOCALE,
  initialLocale: DEFAULT_LOCALE,
  loadingDelay: 200,
  warnOnMissingMessages: import.meta.env.DEV
});
