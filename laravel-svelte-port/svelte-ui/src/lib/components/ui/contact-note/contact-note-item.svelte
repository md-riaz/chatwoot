<script lang="ts">
  import { cn } from '$lib/utils';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    content: string;
    author: {
      name: string;
      avatar?: string;
    };
    createdAt: string;
    onEdit?: () => void;
    onDelete?: () => void;
    class?: string;
  }

  let { content, author, createdAt, onEdit, onDelete, class: className }: Props = $props();
</script>

<div class={cn('p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800', className)}>
  <div class="flex items-start gap-3">
    <Avatar class="h-8 w-8">
      <AvatarImage src={author.avatar} alt={author.name} />
      <AvatarFallback>{author.name.slice(0, 2).toUpperCase()}</AvatarFallback>
    </Avatar>
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2 mb-1">
        <span class="font-medium text-sm text-slate-900 dark:text-slate-100">{author.name}</span>
        <span class="text-xs text-slate-500">{createdAt}</span>
      </div>
      <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{content}</p>
    </div>
    <div class="flex items-center gap-1">
      <Button variant="ghost" size="icon" class="h-6 w-6" onclick={onEdit}>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
        </svg>
      </Button>
      <Button variant="ghost" size="icon" class="h-6 w-6 text-destructive" onclick={onDelete}>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </Button>
    </div>
  </div>
</div>
