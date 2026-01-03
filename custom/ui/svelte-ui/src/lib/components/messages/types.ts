/**
 * Message component types
 */

export type MessageMode = 'reply' | 'private' | 'note';

export interface Attachment {
  id?: string;
  file: File;
  name: string;
  size: number;
  type: string;
  preview?: string;
  uploadProgress?: number;
}

export interface MessageDraft {
  content: string;
  attachments: Attachment[];
  isPrivate: boolean;
  timestamp: number;
}

export interface CannedResponse {
  id: number;
  short_code: string;
  content: string;
}

export interface Mention {
  id: number;
  name: string;
  email: string;
  avatar?: string;
}
