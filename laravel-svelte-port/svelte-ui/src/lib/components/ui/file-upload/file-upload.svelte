<script lang="ts" module>
  export type FileUploadProps = {
    accept?: string;
    multiple?: boolean;
    maxSize?: number; // in bytes
    maxFiles?: number;
    disabled?: boolean;
    class?: string;
    onFilesSelected?: (files: File[]) => void;
    onFilesRemoved?: (file: File) => void;
  };
  
  export type UploadedFile = {
    file: File;
    preview?: string;
    progress?: number;
    error?: string;
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '../button/index.js';
  
  let {
    accept,
    multiple = false,
    maxSize = 10 * 1024 * 1024, // 10MB default
    maxFiles = 10,
    disabled = false,
    class: className,
    onFilesSelected,
    onFilesRemoved,
    ...restProps
  }: FileUploadProps = $props();
  
  let uploadedFiles = $state<UploadedFile[]>([]);
  let isDragging = $state(false);
  let fileInputRef: HTMLInputElement;
  
  function handleDragOver(e: DragEvent) {
    e.preventDefault();
    if (!disabled) {
      isDragging = true;
    }
  }
  
  function handleDragLeave(e: DragEvent) {
    e.preventDefault();
    isDragging = false;
  }
  
  function handleDrop(e: DragEvent) {
    e.preventDefault();
    isDragging = false;
    
    if (disabled) return;
    
    const files = Array.from(e.dataTransfer?.files || []);
    processFiles(files);
  }
  
  function handleFileSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    processFiles(files);
    
    // Reset input
    target.value = '';
  }
  
  function processFiles(files: File[]) {
    const validFiles = files.filter(file => {
      if (maxSize && file.size > maxSize) {
        return false;
      }
      return true;
    });
    
    const filesToAdd = multiple
      ? validFiles.slice(0, maxFiles - uploadedFiles.length)
      : validFiles.slice(0, 1);
    
    const newFiles: UploadedFile[] = filesToAdd.map(file => {
      const uploadedFile: UploadedFile = { file };
      
      // Create preview for images
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          uploadedFile.preview = e.target?.result as string;
        };
        reader.readAsDataURL(file);
      }
      
      return uploadedFile;
    });
    
    if (multiple) {
      uploadedFiles = [...uploadedFiles, ...newFiles];
    } else {
      uploadedFiles = newFiles;
    }
    
    if (onFilesSelected) {
      onFilesSelected(filesToAdd);
    }
  }
  
  function removeFile(index: number) {
    const file = uploadedFiles[index];
    uploadedFiles = uploadedFiles.filter((_, i) => i !== index);
    
    if (onFilesRemoved && file) {
      onFilesRemoved(file.file);
    }
  }
  
  function formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  }
</script>

<div class={cn('w-full', className)} {...restProps}>
  <!-- Drop Zone -->
  <div
    class={cn(
      'relative rounded-lg border-2 border-dashed transition-colors',
      isDragging
        ? 'border-primary bg-primary/10'
        : 'border-border bg-background',
      disabled && 'opacity-50 cursor-not-allowed'
    )}
    role="group"
    aria-label="File upload dropzone"
    ondragover={handleDragOver}
    ondragleave={handleDragLeave}
    ondrop={handleDrop}
  >
    <input
      bind:this={fileInputRef}
      type="file"
      {accept}
      {multiple}
      {disabled}
      class="hidden"
      onchange={handleFileSelect}
    />
    
    <div class="flex flex-col items-center justify-center p-8 text-center">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="48"
        height="48"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="mb-4 text-muted-foreground"
      >
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
        <polyline points="17 8 12 3 7 8" />
        <line x1="12" y1="3" x2="12" y2="15" />
      </svg>
      
      <p class="mb-2 text-sm font-medium">
        {isDragging ? 'Drop files here' : 'Drag and drop files here'}
      </p>
      
      <p class="mb-4 text-xs text-muted-foreground">
        or click to browse
      </p>
      
      <Button
        type="button"
        variant="outline"
        size="sm"
        {disabled}
        onclick={() => fileInputRef?.click()}
      >
        Choose Files
      </Button>
      
      {#if maxSize}
        <p class="mt-2 text-xs text-muted-foreground">
          Maximum file size: {formatFileSize(maxSize)}
        </p>
      {/if}
    </div>
  </div>
  
  <!-- Uploaded Files List -->
  {#if uploadedFiles.length > 0}
    <div class="mt-4 space-y-2">
      {#each uploadedFiles as uploadedFile, index}
        <div class="flex items-center gap-3 rounded-lg border bg-card p-3">
          {#if uploadedFile.preview}
            <img
              src={uploadedFile.preview}
              alt={uploadedFile.file.name}
              class="size-12 rounded object-cover"
            />
          {:else}
            <div class="flex size-12 items-center justify-center rounded bg-muted">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="size-6 text-muted-foreground"
              >
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
              </svg>
            </div>
          {/if}
          
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">
              {uploadedFile.file.name}
            </p>
            <p class="text-xs text-muted-foreground">
              {formatFileSize(uploadedFile.file.size)}
            </p>
            
            {#if uploadedFile.progress !== undefined}
              <div class="mt-1 h-1 w-full rounded-full bg-muted">
                <div
                  class="h-1 rounded-full bg-primary transition-all"
                  style="width: {uploadedFile.progress}%"
                ></div>
              </div>
            {/if}
          </div>
          
          <button
            type="button"
            class="text-muted-foreground hover:text-foreground"
            aria-label="Remove file"
            onclick={() => removeFile(index)}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
      {/each}
    </div>
  {/if}
</div>
