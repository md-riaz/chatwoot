# Internationalization (i18n)

This directory contains i18n configuration and translations for the Chatwoot Svelte application using `svelte-i18n`.

## Setup

The i18n system is configured to support 56 languages with lazy loading for optimal performance.

### Initialization

Initialize i18n in your root `+layout.svelte`:

```svelte
<script lang="ts">
  import { onMount } from 'svelte';
  import { initI18n } from '$lib/i18n';
  
  onMount(async () => {
    await initI18n();
  });
</script>
```

## Usage

### Basic Translation

```svelte
<script lang="ts">
  import { _ } from '$lib/i18n';
</script>

<h1>{$_('dashboard.title')}</h1>
<button>{$_('common.save')}</button>
```

### With Interpolation

```svelte
<script lang="ts">
  import { _ } from '$lib/i18n';
  
  const userName = 'John';
</script>

<p>{$_('welcome.message', { values: { name: userName } })}</p>
```

### Pluralization

```svelte
<script lang="ts">
  import { _ } from '$lib/i18n';
  
  const count = 5;
</script>

<p>
  {$_('conversation.count', {
    values: { n: count },
    default: '{n} conversations'
  })}
</p>
```

### Date Formatting

```svelte
<script lang="ts">
  import { date } from '$lib/i18n';
  import { formatDate, formatRelativeTime } from '$lib/i18n/formatters';
  
  const now = new Date();
  const timestamp = '2024-01-01T12:00:00Z';
</script>

<!-- Using svelte-i18n date formatter -->
<p>{$date(now, { format: 'short' })}</p>

<!-- Using custom formatters -->
<p>{formatDate(timestamp, 'PPpp')}</p>
<p>{formatRelativeTime(timestamp)}</p>
```

### Number Formatting

```svelte
<script lang="ts">
  import { number } from '$lib/i18n';
  import { formatNumber, formatCurrency, formatCompactNumber } from '$lib/i18n/formatters';
  
  const price = 1234.56;
  const views = 1234567;
</script>

<!-- Using svelte-i18n number formatter -->
<p>{$number(price, { style: 'currency', currency: 'USD' })}</p>

<!-- Using custom formatters -->
<p>{formatCurrency(price, 'USD')}</p>
<p>{formatCompactNumber(views)}</p>
```

## Locale Switching

### Locale Selector Component

```svelte
<script lang="ts">
  import { locale, switchLocale, getAvailableLocales } from '$lib/i18n';
  
  const locales = getAvailableLocales();
  
  async function handleLocaleChange(e: Event) {
    const select = e.target as HTMLSelectElement;
    await switchLocale(select.value);
  }
</script>

<select value={$locale} on:change={handleLocaleChange}>
  {#each locales as { code, name }}
    <option value={code}>{name}</option>
  {/each}
</select>
```

### Programmatic Locale Change

```typescript
import { switchLocale } from '$lib/i18n';

// Switch to Spanish
await switchLocale('es');

// Switch to Arabic (RTL)
await switchLocale('ar');
```

## RTL Support

The system automatically handles RTL (Right-to-Left) languages:

```typescript
import { isRTL, getCurrentLocale } from '$lib/i18n';

const currentLocale = getCurrentLocale();
const isRightToLeft = isRTL(currentLocale);

// Document direction is automatically set on locale change
// <html dir="rtl"> or <html dir="ltr">
```

## Supported Locales

56 languages are supported:

- **LTR Languages**: English, Spanish, French, German, Portuguese, etc.
- **RTL Languages**: Arabic, Hebrew, Persian, Urdu

Full list in `SUPPORTED_LOCALES` constant.

## Translation Files

Translation files are organized by locale:

```
src/lib/i18n/locales/
├── en/
│   └── index.json
├── es/
│   └── index.json
├── fr/
│   └── index.json
└── ...
```

### Translation File Structure

```json
{
  "common": {
    "yes": "Yes",
    "no": "No",
    "save": "Save"
  },
  "dashboard": {
    "title": "Dashboard",
    "conversations": "Conversations"
  },
  "errors": {
    "required": "This field is required",
    "server_error": "Server error occurred"
  }
}
```

## Custom Formatters

### Date Formatters

```typescript
import { 
  formatDate,           // Custom date format
  formatRelativeTime,   // "2 hours ago"
  formatSmartDate       // Smart date (today/yesterday/date)
} from '$lib/i18n/formatters';

formatDate(new Date(), 'PPpp');              // "Dec 31, 2023, 11:59 PM"
formatRelativeTime(new Date());              // "2 hours ago"
formatSmartDate(new Date());                 // "11:59 PM" (today)
```

### Number Formatters

```typescript
import {
  formatNumber,         // Localized number
  formatCurrency,       // Currency
  formatPercentage,     // Percentage
  formatCompactNumber,  // 1.2K, 3.4M
  formatFileSize,       // 1.5 MB
  formatDuration        // 2h 30m
} from '$lib/i18n/formatters';

formatNumber(1234.56);                      // "1,234.56"
formatCurrency(99.99, 'USD');               // "$99.99"
formatPercentage(85.5);                     // "85.5%"
formatCompactNumber(1234567);               // "1.2M"
formatFileSize(1536000);                    // "1.46 MB"
formatDuration(7320);                       // "2h 2m"
```

## Migration from vue-i18n

### Vue i18n → svelte-i18n

| vue-i18n | svelte-i18n |
|----------|-------------|
| `$t('key')` | `$_('key')` |
| `$t('key', { name })` | `$_('key', { values: { name } })` |
| `$tc('key', count)` | `$_('key', { values: { n: count } })` |
| `$d(date)` | `$date(date)` |
| `$n(number)` | `$number(number)` |
| `$i18n.locale` | `$locale` |
| `$i18n.locale = 'es'` | `switchLocale('es')` |

### Component Migration

```vue
<!-- Vue -->
<template>
  <h1>{{ $t('dashboard.title') }}</h1>
  <p>{{ $t('welcome', { name: userName }) }}</p>
</template>

<script>
export default {
  data() {
    return {
      userName: 'John'
    };
  }
};
</script>
```

```svelte
<!-- Svelte -->
<script lang="ts">
  import { _ } from '$lib/i18n';
  
  let userName = 'John';
</script>

<h1>{$_('dashboard.title')}</h1>
<p>{$_('welcome', { values: { name: userName } })}</p>
```

## Adding New Translations

1. Create locale directory: `src/lib/i18n/locales/[code]/`
2. Add `index.json` with translations
3. Add locale code to `SUPPORTED_LOCALES` in `index.ts`
4. If RTL, add to `RTL_LOCALES`
5. Add display name to `getLocaleDisplayName()`

## Best Practices

### 1. Use Nested Keys

```json
{
  "conversation": {
    "status": {
      "open": "Open",
      "resolved": "Resolved",
      "pending": "Pending"
    }
  }
}
```

### 2. Keep Keys Descriptive

```typescript
// ✅ Good
$_('conversation.empty_state.title')

// ❌ Bad
$_('conv.empty.t')
```

### 3. Use Interpolation for Dynamic Values

```typescript
// ✅ Good
$_('message.sent_by', { values: { name: userName } })

// ❌ Bad
`Message sent by ${userName}`
```

### 4. Extract Common Strings

```json
{
  "common": {
    "save": "Save",
    "cancel": "Cancel",
    "delete": "Delete"
  }
}
```

### 5. Test RTL Languages

Always test with Arabic or Hebrew to ensure RTL support works correctly.

## Troubleshooting

### Translation Not Showing

1. Check if key exists in translation file
2. Verify locale file is in `locales/[code]/index.json`
3. Check browser console for missing translation warnings
4. Ensure i18n is initialized in root layout

### Locale Not Switching

1. Check if locale code is in `SUPPORTED_LOCALES`
2. Verify translation file exists for that locale
3. Check browser console for loading errors
4. Ensure `switchLocale()` is awaited

### RTL Not Working

1. Verify locale is in `RTL_LOCALES`
2. Check if `document.documentElement.dir` is set
3. Ensure CSS supports RTL (use logical properties)

## Performance Tips

1. **Lazy Loading**: Translations are loaded on-demand
2. **Split Large Files**: Break down translations by feature
3. **Cache Translations**: Browser caches loaded translations
4. **Use Compact Locale Data**: Remove unused keys

## See Also

- [svelte-i18n Documentation](https://github.com/kaisermann/svelte-i18n)
- [date-fns Documentation](https://date-fns.org/)
- Vue i18n implementation in `chatwoot/app/javascript/dashboard/i18n/`
