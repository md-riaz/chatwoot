<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Avatar, AvatarImage, AvatarFallback } from '$lib/components/ui/avatar';

  interface Author {
    name: string;
    thumbnail?: string;
  }

  interface Category {
    title: string;
    slug: string;
    icon?: string;
  }

  let {
    id = 0,
    title = '',
    status = '',
    author,
    category,
    views = 0,
    updatedAt = 0,
    class: className = '',
    onclick = () => {},
    ...restProps
  }: {
    id?: number;
    title?: string;
    status?: string;
    author?: Author;
    category?: Category;
    views?: number;
    updatedAt?: number;
    class?: string;
    onclick?: () => void;
  } = $props();

  function formatDate(timestamp: number): string {
    return new Date(timestamp * 1000).toLocaleDateString();
  }

  function formatViews(count: number): string {
    if (count >= 1000) {
      return `${(count / 1000).toFixed(1)}k`;
    }
    return count.toString();
  }

  const statusVariant = $derived(
    status === 'draft' ? 'secondary' : 
    status === 'archived' ? 'outline' : 
    'default'
  );
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
  {#if category?.icon}
    <span class="text-2xl">{category.icon}</span>
  {/if}

  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2 mb-1">
      <h3 class="font-medium truncate">{title}</h3>
      {#if status}
        <Badge variant={statusVariant} class="text-xs capitalize">
          {status}
        </Badge>
      {/if}
    </div>
    
    <div class="flex items-center gap-4 text-sm text-muted-foreground">
      {#if category}
        <span>{category.title}</span>
      {/if}
      <span>Updated {formatDate(updatedAt)}</span>
      <span>{formatViews(views)} views</span>
    </div>
  </div>

  {#if author}
    <div class="flex items-center gap-2">
      <Avatar class="h-6 w-6">
        {#if author.thumbnail}
          <AvatarImage src={author.thumbnail} alt={author.name} />
        {/if}
        <AvatarFallback class="text-xs">
          {author.name.charAt(0).toUpperCase()}
        </AvatarFallback>
      </Avatar>
      <span class="text-sm text-muted-foreground">{author.name}</span>
    </div>
  {/if}
</div>
