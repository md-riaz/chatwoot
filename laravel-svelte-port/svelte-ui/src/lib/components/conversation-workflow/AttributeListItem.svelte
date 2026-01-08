<script lang="ts">
  /**
   * Attribute List Item Component
   * Displays workflow attribute with edit/delete actions
   */
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Pencil, Trash2, AlignJustify, CircleCheckBig, List, Calendar, Link as LinkIcon, Hash, KeyRound } from 'lucide-svelte';

  interface AttributeBadge {
    type: string;
    label?: string;
  }

  interface Attribute {
    label: string;
    type: string;
    value: string;
    attribute_description?: string;
    description?: string;
  }

  interface Props {
    attribute: Attribute;
    badges?: AttributeBadge[];
    onedit?: (attribute: Attribute) => void;
    ondelete?: (attribute: Attribute) => void;
  }

  let { attribute, badges = [], onedit, ondelete }: Props = $props();

  const iconByType: Record<string, any> = {
    text: AlignJustify,
    checkbox: CircleCheckBig,
    list: List,
    date: Calendar,
    link: LinkIcon,
    number: Hash,
  };

  const attributeIcon = $derived(() => {
    const typeKey = attribute.type?.toLowerCase();
    return iconByType[typeKey] || AlignJustify;
  });

  function handleEdit() {
    onedit?.(attribute);
  }

  function handleDelete() {
    ondelete?.(attribute);
  }
</script>

<div class="flex flex-col gap-2 p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
  <div class="flex flex-wrap gap-2 justify-between items-center">
    <div class="flex flex-wrap gap-2 items-center min-w-0">
      <h4 class="text-sm font-medium truncate text-slate-900 dark:text-slate-100">
        {attribute.label}
      </h4>
      <div class="w-px h-3 bg-slate-300 dark:bg-slate-700" />
      <div class="flex gap-2 items-center text-sm text-slate-600 dark:text-slate-400">
        <div class="flex items-center gap-1.5">
          <svelte:component this={attributeIcon()} class="size-4" />
          <span class="text-sm">{attribute.type}</span>
        </div>
        <div class="w-px h-3 bg-slate-300 dark:bg-slate-700" />
        <div class="flex items-center gap-1.5">
          <KeyRound class="size-4" />
          <span class="line-clamp-1 text-sm">{attribute.value}</span>
        </div>
      </div>
    </div>
    <div class="flex gap-2 items-center">
      {#each badges as badge (badge.type)}
        <Badge variant="outline">{badge.label || badge.type}</Badge>
      {/each}
      {#if badges.length > 0}
        <div class="w-px h-3 bg-slate-300 dark:bg-slate-700 ml-1.5" />
      {/if}
      <Button
        size="sm"
        variant="ghost"
        onclick={handleEdit}
      >
        <Pencil class="h-4 w-4" />
      </Button>
      <div class="w-px h-3 bg-slate-300 dark:bg-slate-700" />
      <Button
        size="sm"
        variant="ghost"
        onclick={handleDelete}
      >
        <Trash2 class="h-4 w-4" />
      </Button>
    </div>
  </div>
  {#if attribute.attribute_description || attribute.description}
    <p class="mb-0 text-sm text-slate-600 dark:text-slate-400">
      {attribute.attribute_description || attribute.description || ''}
    </p>
  {/if}
</div>
