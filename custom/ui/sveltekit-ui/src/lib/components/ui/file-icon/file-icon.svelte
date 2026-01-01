<script lang="ts">
  import { cn } from '$lib/utils';

  type FileType = 
    | 'pdf' | 'doc' | 'docx' | 'xls' | 'xlsx' 
    | 'ppt' | 'pptx' | 'txt' | 'csv' | 'zip' 
    | 'image' | 'audio' | 'video' | 'unknown';

  let {
    type = 'unknown' as FileType,
    size = 'md',
    class: className = '',
    ...restProps
  }: {
    type?: FileType;
    size?: 'sm' | 'md' | 'lg';
    class?: string;
  } = $props();

  const sizeClasses = {
    sm: 'w-6 h-6 text-xs',
    md: 'w-8 h-8 text-sm',
    lg: 'w-12 h-12 text-base',
  };

  const iconConfig: Record<FileType, { bg: string; label: string }> = {
    pdf: { bg: 'bg-red-100 text-red-700', label: 'PDF' },
    doc: { bg: 'bg-blue-100 text-blue-700', label: 'DOC' },
    docx: { bg: 'bg-blue-100 text-blue-700', label: 'DOCX' },
    xls: { bg: 'bg-green-100 text-green-700', label: 'XLS' },
    xlsx: { bg: 'bg-green-100 text-green-700', label: 'XLSX' },
    ppt: { bg: 'bg-orange-100 text-orange-700', label: 'PPT' },
    pptx: { bg: 'bg-orange-100 text-orange-700', label: 'PPTX' },
    txt: { bg: 'bg-gray-100 text-gray-700', label: 'TXT' },
    csv: { bg: 'bg-green-100 text-green-700', label: 'CSV' },
    zip: { bg: 'bg-purple-100 text-purple-700', label: 'ZIP' },
    image: { bg: 'bg-pink-100 text-pink-700', label: 'IMG' },
    audio: { bg: 'bg-yellow-100 text-yellow-700', label: 'AUD' },
    video: { bg: 'bg-indigo-100 text-indigo-700', label: 'VID' },
    unknown: { bg: 'bg-gray-100 text-gray-700', label: 'FILE' },
  };

  const config = $derived(iconConfig[type] || iconConfig.unknown);
</script>

<div
  class={cn(
    'flex items-center justify-center rounded font-medium',
    sizeClasses[size],
    config.bg,
    className
  )}
  {...restProps}
>
  {config.label}
</div>
