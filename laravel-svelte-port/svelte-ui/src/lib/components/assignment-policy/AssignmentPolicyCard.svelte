<script lang="ts">
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Pen, Trash2 } from 'lucide-svelte';

  let {
    id,
    name = '',
    description = '',
    assignmentOrder = '',
    conversationPriority = '',
    assignedInboxCount = 0,
    enabled = false,
    onedit,
    ondelete,
  }: {
    id: number;
    name?: string;
    description?: string;
    assignmentOrder?: string;
    conversationPriority?: string;
    assignedInboxCount?: number;
    enabled?: boolean;
    onedit?: (id: number) => void;
    ondelete?: (id: number) => void;
  } = $props();

  function formatToTitleCase(str: string): string {
    if (!str) return '';
    return str
      .split('_')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }

  let order = $derived(formatToTitleCase(assignmentOrder));
  let priority = $derived(formatToTitleCase(conversationPriority));
</script>

<Card.Root class="transition-all duration-200 hover:shadow-sm">
  <Card.Content class="py-4 px-5">
    <div class="flex flex-col gap-2">
      <!-- Header row -->
      <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-3 min-w-0">
          <h3 class="text-base font-medium text-foreground truncate">{name}</h3>
          <div class="flex items-center gap-2 flex-shrink-0">
            <Badge variant={enabled ? 'default' : 'secondary'} class="text-xs">
              {enabled ? 'Active' : 'Inactive'}
            </Badge>
            {#if assignedInboxCount > 0}
              <span class="text-xs text-muted-foreground">
                {assignedInboxCount} inbox{assignedInboxCount !== 1 ? 'es' : ''}
              </span>
            {/if}
          </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
          <Button
            variant="ghost"
            size="sm"
            class="text-muted-foreground hover:text-foreground px-2"
            onclick={() => onedit?.(id)}
          >
            <Pen class="h-3.5 w-3.5 mr-1" />
            Edit
          </Button>
          <div class="w-px h-3 bg-border"></div>
          <Button
            variant="ghost"
            size="icon"
            class="h-8 w-8 text-muted-foreground hover:text-destructive"
            onclick={() => ondelete?.(id)}
          >
            <Trash2 class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <!-- Description -->
      <p class="text-sm text-muted-foreground truncate">{description}</p>

      <!-- Order / Priority info -->
      {#if order || priority}
        <div class="flex items-center gap-3 text-sm">
          {#if order}
            <span class="text-muted-foreground">
              Order: <span class="text-foreground">{order}</span>
            </span>
          {/if}
          {#if order && priority}
            <div class="w-px h-3 bg-border"></div>
          {/if}
          {#if priority}
            <span class="text-muted-foreground">
              Priority: <span class="text-foreground">{priority}</span>
            </span>
          {/if}
        </div>
      {/if}
    </div>
  </Card.Content>
</Card.Root>
