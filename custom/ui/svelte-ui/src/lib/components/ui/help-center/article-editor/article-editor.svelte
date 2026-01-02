<script lang="ts">
  import { createEventDispatcher, onMount, onDestroy } from 'svelte';
  import { Input } from '../../../input/index.js';
  import { Button } from '../../../button/index.js';
  import { api } from '../../../../api/client.js';

  export let article: Article | null = null;
  export let categories: string[] = [];
  export let locales: string[] = ['en'];

  interface Article {
    id?: string;
    title: string;
    content: string;
    status?: 'draft' | 'published' | 'archived';
    category?: string;
    author?: string;
    tags?: string[];
    featured?: boolean;
    locale?: string;
  }

  const dispatch = createEventDispatcher();

  let form: Article = article ? { ...article } : { title: '', content: '', status: 'draft', tags: [], featured: false, locale: locales[0] };
  let errors: Record<string, string> = {};

  // TipTap editor instance (loaded dynamically)
  let EditorComponent: any = null;
  let editor: any = null;
  let useEditor = false;

  onMount(async () => {
    try {
      const [{ Editor }, StarterKit] = await Promise.all([
        import('@tiptap/svelte').then(m => m.Editor),
        import('@tiptap/starter-kit').then(m => m.default || m)
      ]);

      // create editor instance
      editor = new Editor({
        element: null,
        extensions: [StarterKit()],
        content: form.content || '',
        onUpdate: ({ editor: e }: any) => {
          form.content = e.getHTML();
        }
      });

      EditorComponent = Editor;
      useEditor = true;
    } catch (err) {
      // TipTap not available — fallback to textarea
      useEditor = false;
      console.warn('TipTap not available, using fallback textarea.', err);
    }
  });

  onDestroy(() => {
    if (editor && editor.destroy) editor.destroy();
  });

  function validate() {
    errors = {};
    if (!form.title || form.title.trim() === '') errors.title = 'Title is required';
    if (!form.content || form.content.trim() === '') errors.content = 'Content is required';
    return Object.keys(errors).length === 0;
  }

  async function save() {
    if (!validate()) return;
    try {
      let res;
      const payload = { ...form };
      if (form.id) {
        res = await api.put(`articles/${form.id}`, { json: payload }).json();
      } else {
        res = await api.post('articles', { json: payload }).json();
      }
      dispatch('saved', res);
    } catch (err) {
      console.error('Save failed', err);
      dispatch('error', err);
    }
  }

  function cancel() {
    dispatch('cancel');
  }
</script>

<div class="space-y-4 p-4 max-w-4xl">
  <div>
    <label class="text-sm">Title</label>
    <Input bind:value={form.title} placeholder="Article title" />
    {#if errors.title}
      <p class="text-xs text-destructive">{errors.title}</p>
    {/if}
  </div>

  <div>
    <label class="text-sm">Content</label>
    {#if useEditor && EditorComponent}
      <svelte:component this={EditorComponent} bind:editor={editor} />
    {:else}
      <textarea bind:value={form.content} class="w-full min-h-[240px] rounded border p-2" placeholder="Write article content here..." />
    {/if}
    {#if errors.content}
      <p class="text-xs text-destructive">{errors.content}</p>
    {/if}
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="text-sm">Category</label>
      <select bind:value={form.category} class="w-full">
        <option value="">Select category</option>
        {#each categories as c}
          <option value={c}>{c}</option>
        {/each}
      </select>
    </div>
    <div>
      <label class="text-sm">Locale</label>
      <select bind:value={form.locale} class="w-full">
        {#each locales as l}
          <option value={l}>{l.toUpperCase()}</option>
        {/each}
      </select>
    </div>
  </div>

  <div class="flex items-center justify-end gap-2">
    <Button variant="ghost" on:click={cancel}>Cancel</Button>
    <Button on:click={save}>{form.id ? 'Update' : 'Create'}</Button>
  </div>
</div>
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
