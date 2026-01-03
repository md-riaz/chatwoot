<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Badge } from '$lib/components/ui/badge';
  import { Switch } from '$lib/components/ui/switch';

  export let article: {
    id?: string;
    title: string;
    content: string;
    status: 'draft' | 'published' | 'archived';
    category: string;
    author: string;
    tags: string[];
    featured: boolean;
    locale: string;
  } = {
    title: '',
    content: '',
    status: 'draft',
    category: '',
    author: '',
    tags: [],
    featured: false,
    locale: 'en'
  };

  export let categories: string[] = [];
  export let locales: string[] = ['en', 'es', 'fr', 'de'];
  export let onSave: (article: typeof article) => void = () => {};
  export let onPublish: (article: typeof article) => void = () => {};
  export let onCancel: () => void = () => {};
  export let saving = false;

  let newTag = '';
  let charCount = 0;

  $: charCount = article.content.length;

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
      <Button variant="outline" on:click={onCancel}>
        Cancel
      </Button>
      <Button variant="outline" on:click={handleSave} disabled={saving}>
        Save Draft
      </Button>
      <Button on:click={handlePublish} disabled={saving || !article.title || !article.content}>
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
        <select
          id="category"
          bind:value={article.category}
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          <option value="">Select category</option>
          {#each categories as cat}
            <option value={cat}>{cat}</option>
          {/each}
        </select>
      </div>

      <div class="space-y-2">
        <Label for="locale">Locale</Label>
        <select
          id="locale"
          bind:value={article.locale}
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
          {#each locales as loc}
            <option value={loc}>{loc.toUpperCase()}</option>
          {/each}
        </select>
      </div>
    </div>

    <div class="space-y-2">
      <Label>Tags</Label>
      <div class="flex gap-2">
        <Input
          bind:value={newTag}
          placeholder="Add tag"
          on:keydown={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
        />
        <Button type="button" variant="outline" on:click={addTag}>
          Add
        </Button>
      </div>
      {#if article.tags.length > 0}
        <div class="flex flex-wrap gap-2 mt-2">
          {#each article.tags as tag}
            <Badge variant="secondary" class="cursor-pointer" on:click={() => removeTag(tag)}>
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
