# Utilities

Comprehensive utility functions migrated from Vue application to TypeScript.

## Available Utilities

### URL Utilities (`url.ts`)

Functions for URL construction and manipulation:

- `frontendURL(path, params?)` - Build app URLs with query parameters
- `conversationURL(options)` - Build conversation URLs with context
- `conversationListPageURL(options)` - Build conversation list URLs
- `contactURL(accountId, contactId)` - Build contact URLs
- `settingsURL(accountId, section?)` - Build settings URLs
- `reportsURL(accountId, reportType?)` - Build reports URLs
- `isValidURL(value)` - Validate URL format
- `parseQueryString(queryString)` - Parse query string to object
- `buildQueryString(params)` - Build query string from object
- `addQueryParams(url, params)` - Add parameters to URL
- `getDomain(url)` - Extract domain from URL
- `isExternalURL(url)` - Check if URL is external

### Validation Utilities (`validation.ts`)

Common validation functions:

- `isEmpty(value)` - Check if value is empty
- `isValidEmail(email)` - Validate email address
- `isValidPhone(phone)` - Validate phone number
- `isValidURL(url)` - Validate URL
- `isValidJSON(value)` - Validate JSON string
- `isRequired(value)` - Check required field
- `minLength(value, min)` - Validate minimum length
- `maxLength(value, max)` - Validate maximum length
- `inRange(value, min, max)` - Validate number range
- `isStrongPassword(password)` - Validate password strength
- `isValidHexColor(color)` - Validate hex color code
- `isValidSlug(slug)` - Validate URL slug
- `isValidIPv4(ip)` - Validate IPv4 address
- `isValidCreditCard(cardNumber)` - Validate credit card (Luhn)
- `matchesPattern(value, pattern)` - Pattern matching
- `valuesMatch(value1, value2)` - Check if values match
- `validateFilter(filter)` - Validate automation filter
- `VALIDATION_MESSAGES` - Standard error messages

### Format Utilities (`format.ts`)

Functions for formatting data:

- `formatFileSize(bytes)` - Format bytes to KB/MB/GB
- `formatNumber(num, locale?)` - Format with thousand separators
- `formatCurrency(amount, currency, locale?)` - Format currency
- `formatPercentage(value, decimals?)` - Format percentage
- `formatDuration(seconds)` - Format to "1h 2m 3s"
- `formatCompactNumber(num)` - Format to "1.2M", "3.4K"
- `formatPhoneNumber(phone)` - Format phone number
- `truncate(str, maxLength)` - Truncate with ellipsis
- `capitalize(str)` - Capitalize first letter
- `titleCase(str)` - Capitalize all words
- `toSlug(str)` - Convert to URL slug (kebab-case)
- `toAttributeSlug(str)` - Convert to snake_case
- `toCategorySlug(str)` - Convert to kebab-case
- `formatInitials(name, maxChars?)` - Get initials from name
- `pluralize(word, count)` - Pluralize word
- `formatList(items, useOxfordComma?)` - Format array as list
- `stripHTML(html)` - Remove HTML tags
- `escapeHTML(str)` - Escape HTML characters
- `unescapeHTML(str)` - Unescape HTML entities

### Color Utilities (`color.ts`)

Functions for color manipulation:

- `hexToRgb(hex)` - Convert hex to RGB object
- `rgbToHex(r, g, b)` - Convert RGB to hex
- `hexToHsl(hex)` - Convert hex to HSL
- `rgbToHsl(r, g, b)` - Convert RGB to HSL
- `lighten(hex, percent)` - Lighten color
- `darken(hex, percent)` - Darken color
- `adjustBrightness(hex, amount)` - Adjust brightness
- `getContrastRatio(hex1, hex2)` - Calculate contrast ratio
- `getContrastTextColor(bgHex)` - Get 'light' or 'dark' text color
- `meetsWCAGAA(textColor, bgColor, isLargeText?)` - Check WCAG AA compliance
- `meetsWCAGAAA(textColor, bgColor, isLargeText?)` - Check WCAG AAA compliance
- `randomColor()` - Generate random hex color
- `blendColors(color1, color2, weight?)` - Blend two colors
- `getColorPalette(baseColor, steps?)` - Generate color palette
- `isValidHex(color)` - Validate hex color

### File Utilities (`file.ts`)

Functions for file operations:

- `formatFileSize(bytes)` - Format file size
- `getFileExtension(filename)` - Get file extension
- `getFileNameWithoutExtension(filename)` - Get filename without ext
- `isImageFile(filename)` - Check if image
- `isVideoFile(filename)` - Check if video
- `isAudioFile(filename)` - Check if audio
- `isDocumentFile(filename)` - Check if document
- `isArchiveFile(filename)` - Check if archive
- `getFileType(filename)` - Get file type category
- `getMimeType(filename)` - Get MIME type from extension
- `validateFileSize(file, maxSizeMB)` - Validate file size
- `validateFileType(file, allowedTypes)` - Validate file type
- `readFileAsText(file)` - Read file as text
- `readFileAsDataURL(file)` - Read file as data URL
- `readFileAsArrayBuffer(file)` - Read file as array buffer
- `downloadFile(url, filename)` - Download file from URL
- `downloadData(data, filename, mimeType?)` - Download data as file
- `dataURLToBlob(dataURL)` - Convert data URL to Blob
- `blobToDataURL(blob)` - Convert Blob to data URL
- `compressImage(file, maxWidth?, maxHeight?, quality?)` - Compress image

## Usage Examples

### URL Building

```typescript
import { frontendURL, conversationURL } from '$lib/utils/url';

// Build app URL with query params
const url = frontendURL('dashboard', { page: 2, status: 'open' });
// => '/app/dashboard?page=2&status=open'

// Build conversation URL
const convUrl = conversationURL({
  accountId: 1,
  id: 123,
  activeInbox: 5,
});
// => 'accounts/1/inbox/5/conversations/123'
```

### Validation

```typescript
import { isValidEmail, isStrongPassword, VALIDATION_MESSAGES } from '$lib/utils/validation';

if (!isValidEmail(email)) {
  errors.email = VALIDATION_MESSAGES.INVALID_EMAIL;
}

if (!isStrongPassword(password)) {
  errors.password = VALIDATION_MESSAGES.WEAK_PASSWORD;
}
```

### Formatting

```typescript
import { formatFileSize, formatDuration, formatCompactNumber } from '$lib/utils/format';

formatFileSize(1048576); // "1 MB"
formatDuration(3665); // "1h 1m 5s"
formatCompactNumber(1234567); // "1.2M"
```

### Color Manipulation

```typescript
import { lighten, darken, getContrastTextColor, meetsWCAGAA } from '$lib/utils/color';

const lightColor = lighten('#ff5733', 20); // Lighten by 20%
const darkColor = darken('#ff5733', 20); // Darken by 20%

const textColor = getContrastTextColor('#ff5733'); // 'light' or 'dark'

// Check accessibility
if (meetsWCAGAA('#ffffff', '#000000')) {
  // Color combination is accessible
}
```

### File Operations

```typescript
import {
  getFileType,
  validateFileSize,
  readFileAsDataURL,
  compressImage,
} from '$lib/utils/file';

const type = getFileType('image.jpg'); // 'image'

if (!validateFileSize(file, 10)) {
  // File exceeds 10 MB
}

// Read image
const dataURL = await readFileAsDataURL(file);

// Compress image
const compressed = await compressImage(file, 1920, 1080, 0.8);
```

## Vue Migration

These utilities replace the following Vue helper files:

| Vue Helper                          | Svelte Utility   |
| ----------------------------------- | ---------------- |
| `dashboard/helper/URLHelper.js`     | `utils/url.ts`   |
| `dashboard/helper/validations.js`   | `utils/validation.ts` |
| `dashboard/helper/commons.js`       | `utils/format.ts` |
| `dashboard/helper/labelColor.js`    | `utils/color.ts` |
| `dashboard/helper/uploadHelper.js`  | `utils/file.ts`  |
| `dashboard/helper/downloadHelper.js`| `utils/file.ts`  |

## TypeScript Benefits

- ✅ **Type Safety**: All functions are fully typed
- ✅ **Autocomplete**: IDE provides parameter hints
- ✅ **Compile-time Errors**: Catch errors before runtime
- ✅ **Self-documenting**: Types serve as documentation
- ✅ **Refactoring**: Safe and easy refactoring

## Testing

All utility functions should be tested with comprehensive unit tests. Example:

```typescript
import { describe, it, expect } from 'vitest';
import { formatFileSize } from '$lib/utils/file';

describe('formatFileSize', () => {
  it('formats bytes correctly', () => {
    expect(formatFileSize(0)).toBe('0 Bytes');
    expect(formatFileSize(1024)).toBe('1 KB');
    expect(formatFileSize(1048576)).toBe('1 MB');
  });
});
```

## Best Practices

1. **Pure Functions**: All utilities are pure functions (no side effects)
2. **Null Safety**: Handle null/undefined inputs gracefully
3. **Type Guards**: Use TypeScript type guards for runtime safety
4. **Performance**: Optimized for performance with memoization where needed
5. **Error Handling**: Return sensible defaults instead of throwing errors
6. **Documentation**: JSDoc comments for all functions

## Adding New Utilities

When adding new utility functions:

1. Choose the appropriate file based on functionality
2. Add TypeScript types and JSDoc comments
3. Follow existing patterns and naming conventions
4. Add unit tests
5. Update this README with examples
