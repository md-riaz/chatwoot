<script lang="ts">
  import { cn } from '$lib/utils';
  import { FileIcon } from '$lib/components/ui/file-icon';
  import { Badge } from '$lib/components/ui/badge';

  type DocumentStatus = 'processing' | 'completed' | 'failed';

  let {
    id = 0,
    name = '',
    type = 'unknown',
    size = '',
    status = 'completed' as DocumentStatus,
    uploadedAt = '',
    class: className = '',
    onclick = () => {},
    ...restProps
  }: {
    id?: number;
    name?: string;
    type?: string;
    size?: string;
    status?: DocumentStatus;
    uploadedAt?: string;
    class?: string;
    onclick?: () => void;
  } = $props();

  function formatDate(dateString: string): string {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString();
  }

  const statusConfig: Record<DocumentStatus, { variant: 'default' | 'secondary' | 'destructive'; label: string }> = {
    processing: { variant: 'secondary', label: 'Processing' },
    completed: { variant: 'default', label: 'Ready' },
    failed: { variant: 'destructive', label: 'Failed' },
  };
</script>

<div
  class={cn(
    'flex items-center gap-4 p-4 border rounded-lg bg-card hover:bg-accent/50 cursor-pointer transition-colors',
    className
  )}
  onclick={onclick}
  role="button"
  tabindex="0"
  {...restProps}
>
  <FileIcon type={type as any} size="lg" />

  <div class="flex-1 min-w-0">
    <h3 class="font-medium truncate">{name}</h3>
    <div class="flex items-center gap-3 text-sm text-muted-foreground">
      {#if size}
        <span>{size}</span>
      {/if}
      {#if uploadedAt}
        <span>Uploaded {formatDate(uploadedAt)}</span>
      {/if}
    </div>
  </div>

  <Badge variant={statusConfig[status].variant}>
    {statusConfig[status].label}
  </Badge>
</div>
