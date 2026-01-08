<script lang="ts" context="module">
  export type EmojiPickerProps = {
    onEmojiSelect?: (emoji: string) => void;
    class?: string;
  };
  
  export type EmojiCategory = {
    name: string;
    icon: string;
    emojis: string[];
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  
  let {
    onEmojiSelect,
    class: className,
    ...restProps
  }: EmojiPickerProps = $props();
  
  let searchQuery = $state('');
  let selectedCategory = $state('smileys');
  let recentEmojis = $state<string[]>([]);
  
  const categories: Record<string, EmojiCategory> = {
    recent: {
      name: 'Recently Used',
      icon: '🕐',
      emojis: []
    },
    smileys: {
      name: 'Smileys & Emotion',
      icon: '😀',
      emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '😶‍🌫️', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓', '🧐']
    },
    people: {
      name: 'People & Body',
      icon: '👋',
      emojis: ['👋', '🤚', '🖐', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🦷', '🦴', '👀', '👁', '👅', '👄', '💋']
    },
    nature: {
      name: 'Animals & Nature',
      icon: '🐶',
      emojis: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🦟', '🦗', '🕷', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈']
    },
    food: {
      name: 'Food & Drink',
      icon: '🍕',
      emojis: ['🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶', '🫑', '🌽', '🥕', '🫒', '🧄', '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🫓', '🥪', '🥙', '🧆', '🌮', '🌯', '🫔', '🥗']
    },
    activity: {
      name: 'Activities',
      icon: '⚽',
      emojis: ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🪃', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛼', '🛷', '⛸', '🥌', '🎿', '⛷', '🏂', '🪂', '🏋️', '🤼', '🤸', '🤺', '⛹️', '🤾', '🏌️', '🏇', '🧘', '🏊', '🤽', '🚣', '🧗', '🚵', '🚴', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖', '🏵', '🎗']
    },
    travel: {
      name: 'Travel & Places',
      icon: '🚗',
      emojis: ['🚗', '🚕', '🚙', '🚌', '🚎', '🏎', '🚓', '🚑', '🚒', '🚐', '🚚', '🚛', '🚜', '🦯', '🦽', '🦼', '🛴', '🚲', '🛵', '🏍', '🛺', '🚨', '🚔', '🚍', '🚘', '🚖', '🚡', '🚠', '🚟', '🚃', '🚋', '🚞', '🚝', '🚄', '🚅', '🚈', '🚂', '🚆', '🚇', '🚊', '🚉', '✈️', '🛫', '🛬', '🛩', '💺', '🛰', '🚁', '🛸', '🚀', '🛶', '⛵', '🚤', '🛥', '🛳', '⛴', '🚢', '⚓', '⛽']
    },
    objects: {
      name: 'Objects',
      icon: '⚽',
      emojis: ['⌚', '📱', '📲', '💻', '⌨️', '🖥', '🖨', '🖱', '🖲', '🕹', '🗜', '💽', '💾', '💿', '📀', '📼', '📷', '📸', '📹', '🎥', '📽', '🎞', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙', '🎚', '🎛', '🧭', '⏱', '⏲', '⏰', '🕰', '⌛', '⏳', '📡', '🔋', '🔌', '💡', '🔦', '🕯', '🪔', '🧯', '🛢', '💸', '💵', '💴', '💶', '💷', '🪙', '💰', '💳', '🪪', '💎', '⚖️', '🪜', '🧰', '🪛', '🔧']
    },
    symbols: {
      name: 'Symbols',
      icon: '❤️',
      emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️']
    }
  };
  
  let filteredEmojis = $derived(() => {
    if (searchQuery) {
      // Simple search - in a real app, you'd search by emoji names
      return Object.values(categories)
        .flatMap(cat => cat.emojis)
        .filter(emoji => emoji.includes(searchQuery));
    }
    
    if (selectedCategory === 'recent') {
      return recentEmojis;
    }
    
    return categories[selectedCategory]?.emojis || [];
  });
  
  function selectEmoji(emoji: string) {
    // Add to recent emojis
    recentEmojis = [emoji, ...recentEmojis.filter(e => e !== emoji)].slice(0, 20);
    
    if (onEmojiSelect) {
      onEmojiSelect(emoji);
    }
  }
</script>

<div class={cn('w-full max-w-sm rounded-lg border bg-popover p-3 shadow-md', className)} {...restProps}>
  <!-- Search -->
  <div class="mb-3">
    <input
      type="text"
      placeholder="Search emoji..."
      bind:value={searchQuery}
      class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
    />
  </div>
  
  <!-- Category Tabs -->
  <div class="mb-3 flex gap-1 overflow-x-auto pb-2">
    {#if recentEmojis.length > 0}
      <button
        type="button"
        class={cn(
          'flex size-8 items-center justify-center rounded-md text-lg transition-colors hover:bg-accent',
          selectedCategory === 'recent' && 'bg-accent'
        )}
        onclick={() => { selectedCategory = 'recent'; }}
        title="Recently Used"
      >
        {categories.recent.icon}
      </button>
    {/if}
    {#each Object.entries(categories) as [key, category]}
      {#if key !== 'recent'}
        <button
          type="button"
          class={cn(
            'flex size-8 items-center justify-center rounded-md text-lg transition-colors hover:bg-accent',
            selectedCategory === key && 'bg-accent'
          )}
          onclick={() => { selectedCategory = key; }}
          title={category.name}
        >
          {category.icon}
        </button>
      {/if}
    {/each}
  </div>
  
  <!-- Emoji Grid -->
  <div class="grid grid-cols-8 gap-1 max-h-64 overflow-y-auto">
    {#each filteredEmojis() as emoji}
      <button
        type="button"
        class="flex size-8 items-center justify-center rounded-md text-xl hover:bg-accent transition-colors"
        onclick={() => selectEmoji(emoji)}
      >
        {emoji}
      </button>
    {:else}
      <div class="col-span-8 py-8 text-center text-sm text-muted-foreground">
        No emojis found
      </div>
    {/each}
  </div>
</div>
