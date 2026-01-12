/**
 * API Error handling utilities
 */

export interface ApiErrorData {
  message?: string;
  error?: string | Record<string, string[]>;
  errors?: Record<string, string[]>;
  details?: Record<string, string[]>;
  [key: string]: any;
}

/**
 * Custom API Error class
 */
export class ApiError extends Error {
  public status: number;
  public data: ApiErrorData;
  public endpoint?: string;

  constructor(status: number, message: string, data: ApiErrorData = {}, endpoint?: string) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
    this.endpoint = endpoint;

    // Maintains proper stack trace for where error was thrown (V8 only)
    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, ApiError);
    }
  }

  /**
   * Check if error is an authentication error
   */
  isAuthError(): boolean {
    return this.status === 401;
  }

  /**
   * Check if error is a forbidden error
   */
  isForbiddenError(): boolean {
    return this.status === 403;
  }

  /**
   * Check if error is a validation error
   */
  isValidationError(): boolean {
    return this.status === 422;
  }

  /**
   * Check if error is a server error
   */
  isServerError(): boolean {
    return this.status >= 500;
  }

  /**
   * Check if error is a rate limit error
   */
  isRateLimitError(): boolean {
    return this.status === 429;
  }

  /**
   * Get formatted error message
   */
  getFormattedMessage(): string {
    // Support both 'errors' and 'details' fields (Laravel uses 'details')
    const validationErrors = this.data.errors || this.data.details;
    
    if (validationErrors) {
      const errorMessages = Object.entries(validationErrors)
        .map(([field, messages]) => {
          // Handle both array and single string messages
          const messageArray = Array.isArray(messages) ? messages : [messages];
          // Clean up field names (remove 'settings.' prefix if present)
          const cleanField = field.replace(/^settings\./, '');
          return `${cleanField}: ${messageArray.join(', ')}`;
        })
        .join('; ');
      return errorMessages || this.message;
    }
    
    // Fallback to error field if it's a string
    if (typeof this.data.error === 'string') {
      return this.data.error;
    }
    
    return this.data.message || this.message;
  }
}

/**
 * Network Error class
 */
export class NetworkError extends Error {
  constructor(message = 'Network error occurred') {
    super(message);
    this.name = 'NetworkError';

    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, NetworkError);
    }
  }
}

/**
 * Handle HTTP errors and create appropriate error instances
 */
export async function handleHttpError(response: Response, endpoint?: string): Promise<never> {
  let data: ApiErrorData = {};
  
  try {
    const text = await response.text();
    if (text) {
      data = JSON.parse(text);
    }
  } catch (e) {
    // Response body is not JSON or empty
    data = { message: response.statusText };
  }

  const message = data.message || response.statusText || 'Request failed';
  throw new ApiError(response.status, message, data, endpoint);
}

/**
 * Check if error is an ApiError instance
 */
export function isApiError(error: unknown): error is ApiError {
  return error instanceof ApiError;
}

/**
 * Check if error is a NetworkError instance
 */
export function isNetworkError(error: unknown): error is NetworkError {
  return error instanceof NetworkError;
}

/**
 * Get user-friendly error message from any error
 */
export function getErrorMessage(error: unknown): string {
  if (isApiError(error)) {
    return error.getFormattedMessage();
  }
  
  if (isNetworkError(error)) {
    return error.message;
  }
  
  if (error instanceof Error) {
    return error.message;
  }
  
  return 'An unexpected error occurred';
}
