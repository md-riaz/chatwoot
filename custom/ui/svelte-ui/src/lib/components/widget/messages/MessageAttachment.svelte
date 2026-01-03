<script lang="ts">
  import type { Attachment } from '$lib/widget/api/types';
  import { FileText, Image as ImageIcon, Video } from 'lucide-svelte';

  interface Props {
    attachment: Attachment;
  }

  let { attachment }: Props = $props();

  const isImage = $derived(attachment.fileType.startsWith('image/'));
  const isVideo = $derived(attachment.fileType.startsWith('video/'));

  function formatFileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  const fileSize = $derived(formatFileSize(attachment.fileSize));
</script>

<div class="message-attachment">
  {#if isImage}
    <a href={attachment.dataUrl} target="_blank" rel="noopener noreferrer">
      <img
        src={attachment.thumbUrl || attachment.dataUrl}
        alt={attachment.fileName}
        class="attachment-image"
      />
    </a>
  {:else if isVideo}
    <video src={attachment.dataUrl} controls class="attachment-video">
      <track kind="captions" />
    </video>
  {:else}
    <a
      href={attachment.dataUrl}
      download={attachment.fileName}
      class="attachment-file"
      target="_blank"
      rel="noopener noreferrer"
    >
      <div class="file-icon">
        <FileText size={20} />
      </div>
      <div class="file-info">
        <div class="file-name">{attachment.fileName}</div>
        <div class="file-size">{fileSize}</div>
      </div>
    </a>
  {/if}
</div>

<style>
  .message-attachment {
    border-radius: 8px;
    overflow: hidden;
  }

  .attachment-image {
    width: 100%;
    max-width: 300px;
    height: auto;
    display: block;
    border-radius: 8px;
    cursor: pointer;
    transition: opacity 0.2s ease;
  }

  .attachment-image:hover {
    opacity: 0.9;
  }

  .attachment-video {
    width: 100%;
    max-width: 300px;
    border-radius: 8px;
  }

  .attachment-file {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s ease;
  }

  .attachment-file:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  .file-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .file-info {
    flex: 1;
    min-width: 0;
  }

  .file-name {
    font-size: 13px;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .file-size {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 2px;
  }
</style>
