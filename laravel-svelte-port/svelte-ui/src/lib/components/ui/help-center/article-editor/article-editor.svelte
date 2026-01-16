<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Badge } from '$lib/components/ui/badge';
  import { Switch } from '$lib/components/ui/switch';
  import * as Select from '$lib/components/ui/select';

  interface Article {
    id?: string;
    title: string;
    content: string;
    status: 'draft' | 'published' | 'archived';
    category: string;
    author: string;
    tags: string[];
    featured: boolean;
    locale: string;
  }

  interface Props {
    article?: Article;
    categories?: string[];
    locales?: string[];
    onSave?: (article: Article) => void;
    onPublish?: (article: Article) => void;
    onCancel?: () => void;
    saving?: boolean;
  }

  let {
    article = {
      title: '',
      content: '',
      status: 'draft',
      category: '',
      author: '',
      tags: [],
      featured: false,
      locale: 'en'
    },
    categories = [],
    locales = ['en', 'es', 'fr', 'de'],
    onSave = () => {},
    onPublish = () => {},
    onCancel = () => {},
    saving = false
  }: Props = $props();

  let newTag = '';
  let charCount = 0;

  // Use string values directly for shadcn-svelte select
  let categoryValue = $state<string>(article.category);
  let localeValue = $state<string>(article.locale);

  // Sync back to article
  $effect(() => {
    article.category = categoryValue;
  });

  $effect(() => {
    article.locale = localeValue;
  });

  // Sync from article when it changes externally
  $effect(() => {
    categoryValue = article.category;
    localeValue = article.locale;
  });

  // Use $derived instead of $: for reactive statements
  charCount = $derived(article.content.length);

  function addTag() {
    if (newTag.trim() && !article.tags.includes(newTag.trim())) {
      article.tags = [...article.tags, newTag.trim()];
      newTag = '';
    }
  }

  function removeTag(tag: string) {
    article.tags = article.tags.filter(t => t !== tag);
  }

  function handleSave() {
    onSave(article);
  }

  function handlePublish() {
    article.status = 'published';
    onPublish(article);
  }
</script>

<div class="w-full max-w-4xl mx-auto space-y-6 p-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold">
      {article.id ? 'Edit Article' : 'New Article'}
    </h2>
    <div class="flex gap-2">
      <Button variant="outline" onclick={onCancel}>
        Cancel
      </Button>
      <Button variant="outline" onclick={handleSave} disabled={saving}>
        Save Draft
      </Button>
      <Button onclick={handlePublish} disabled={saving || !article.title || !article.content}>
        Publish
      </Button>
    </div>
  </div>

  <div class="grid gap-6">
    <div class="space-y-2">
      <Label for="title">Title*</Label>
      <Input
        id="title"
        bind:value={article.title}
        placeholder="Enter article title"
        class="text-lg"
      />
    </div>

    <div class="space-y-2">
      <Label for="content">Content*</Label>
      <Textarea
        id="content"
        bind:value={article.content}
        placeholder="Write your article content here..."
        rows={12}
        class="font-mono"
      />
      <div class="text-sm text-muted-foreground">
        {charCount} characters
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label for="category">Category</Label>
        <Select.Root bind:value={categoryValue} type="single">
          <Select.Trigger id="category">
            <Select.Value placeholder="Select category" />
          </Select.Trigger>
          <Select.Content>
            {#each categories as cat}
              <Select.Item value={cat}>{cat}</Select.Item>
            {/each}
          </Select.Content>
        </Select.Root>
      </div>

      <div class="space-y-2">
        <Label for="locale">Locale</Label>
        <Select.Root bind:value={localeValue} type="single">
          <Select.Trigger id="locale">
            <Select.Value placeholder="Select locale" />
          </Select.Trigger>
          <Select.Content>
            {#each locales as loc}
              <Select.Item value={loc}>{loc.toUpperCase()}</Select.Item>
            {/each}
          </Select.Content>
        </Select.Root>
      </div>
    </div>

    <div class="space-y-2">
      <Label>Tags</Label>
      <div class="flex gap-2">
        <Input
          bind:value={newTag}
          placeholder="Add tag"
          onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && (e.preventDefault(), addTag())}
        />
        <Button type="button" variant="outline" onclick={addTag}>
          Add
        </Button>
      </div>
      {#if article.tags.length > 0}
        <div class="flex flex-wrap gap-2 mt-2">
          {#each article.tags as tag}
            <Badge variant="secondary" class="cursor-pointer" onclick={() => removeTag(tag)}>
              {tag} ×
            </Badge>
          {/each}
        </div>
      {/if}
    </div>

    <div class="flex items-center space-x-2">
      <Switch id="featured" bind:checked={article.featured} />
      <Label for="featured">Featured article</Label>
    </div>

    <div class="space-y-2">
      <Label for="author">Author</Label>
      <Input
        id="author"
        bind:value={article.author}
        placeholder="Author name"
      />
    </div>

    <div class="space-y-2">
      <Label>Status</Label>
      <div class="flex gap-2">
        <Badge variant={article.status === 'draft' ? 'default' : 'outline'}>
          {article.status}
        </Badge>
      </div>
    </div>
  </div>
</div>
