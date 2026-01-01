<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    title: string;
    content: string;
    category?: string;
    isActive?: boolean;
    onEdit?: () => void;
    onDelete?: () => void;
    class?: string;
  }

  let { title, content, category, isActive = true, onEdit, onDelete, class: className }: Props = $props();
</script>

<div class={cn(
  'p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
  className
)}>
  <div class="flex items-start justify-between gap-2 mb-2">
    <h4 class="font-medium text-slate-900 dark:text-slate-100">{title}</h4>
    <div class="flex items-center gap-1">
      {#if category}
        <Badge variant="outline">{category}</Badge>
      {/if}
      <Badge variant={isActive ? 'success' : 'secondary'}>{isActive ? 'Active' : 'Inactive'}</Badge>
    </div>
  </div>
  <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mb-3">{content}</p>
  <div class="flex items-center gap-2">
    <Button variant="outline" size="sm" onclick={onEdit}>Edit</Button>
    <Button variant="ghost" size="sm" class="text-destructive" onclick={onDelete}>Delete</Button>
  </div>
</div>
