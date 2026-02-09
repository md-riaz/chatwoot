/**
 * Download Helper Utilities
 * Functions for generating file names and handling downloads
 */

interface GenerateFileNameParams {
  type: string;
  to: number | Date;
  businessHours?: boolean;
}

/**
 * Generates a standardized file name for report downloads
 * @param params - Parameters for file name generation
 * @returns Formatted file name string
 */
export function generateFileName(params: GenerateFileNameParams): string {
  const { type, to, businessHours = false } = params;

  // Convert timestamp to date
  const date = typeof to === 'number' ? new Date(to * 1000) : to;
  
  // Format date as YYYY-MM-DD
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const dateStr = `${year}-${month}-${day}`;

  // Build file name
  const businessHoursSuffix = businessHours ? '_business_hours' : '';
  return `${type}_report_${dateStr}${businessHoursSuffix}.csv`;
}

/**
 * Triggers a file download in the browser
 * @param data - File data (Blob, string, or ArrayBuffer)
 * @param fileName - Name for the downloaded file
 * @param mimeType - MIME type of the file (default: text/csv)
 */
export function downloadFile(
  data: Blob | string | ArrayBuffer,
  fileName: string,
  mimeType: string = 'text/csv'
): void {
  let blob: Blob;

  if (data instanceof Blob) {
    blob = data;
  } else if (typeof data === 'string') {
    blob = new Blob([data], { type: mimeType });
  } else {
    blob = new Blob([data], { type: mimeType });
  }

  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Converts data to CSV format
 * @param data - Array of objects to convert
 * @param headers - Optional custom headers
 * @returns CSV string
 */
export function convertToCSV(
  data: Record<string, any>[],
  headers?: string[]
): string {
  if (!data || data.length === 0) {
    return '';
  }

  const keys = headers || Object.keys(data[0]);
  const csvHeaders = keys.join(',');
  
  const csvRows = data.map(row => {
    return keys.map(key => {
      const value = row[key];
      // Escape quotes and wrap in quotes if contains comma or quote
      if (value === null || value === undefined) return '';
      const stringValue = String(value);
      if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
        return `"${stringValue.replace(/"/g, '""')}"`;
      }
      return stringValue;
    }).join(',');
  });

  return [csvHeaders, ...csvRows].join('\n');
}
