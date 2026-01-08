<script lang="ts">
  import { Frown, Meh, Smile, Star } from 'lucide-svelte';

  interface Props {
    type?: 'emoji' | 'star';
    value: number;
    onchange: (value: number) => void;
    disabled?: boolean;
  }

  let { type = 'emoji', value = 0, onchange, disabled = false }: Props = $props();

  const emojiRatings = [
    { value: 1, icon: Frown, label: 'Very Dissatisfied', color: '#ef4444' },
    { value: 2, icon: Frown, label: 'Dissatisfied', color: '#f97316' },
    { value: 3, icon: Meh, label: 'Neutral', color: '#eab308' },
    { value: 4, icon: Smile, label: 'Satisfied', color: '#84cc16' },
    { value: 5, icon: Smile, label: 'Very Satisfied', color: '#22c55e' },
  ];

  function handleSelect(rating: number) {
    if (!disabled) {
      onchange(rating);
    }
  }
</script>

<div class="rating-input">
  {#if type === 'emoji'}
    <div class="emoji-ratings">
      {#each emojiRatings as rating}
        <button
          class="emoji-button"
          class:selected={value === rating.value}
          style:--rating-color={rating.color}
          onclick={() => handleSelect(rating.value)}
          {disabled}
          aria-label={rating.label}
        >
          <svelte:component this={rating.icon} size={40} />
        </button>
      {/each}
    </div>
  {:else}
    <div class="star-ratings">
      {#each Array(5) as _, i}
        <button
          class="star-button"
          class:selected={value >= i + 1}
          onclick={() => handleSelect(i + 1)}
          {disabled}
          aria-label={`${i + 1} star${i > 0 ? 's' : ''}`}
        >
          <Star size={40} fill={value >= i + 1 ? '#fbbf24' : 'none'} />
        </button>
      {/each}
    </div>
  {/if}
</div>

<style>
  .rating-input {
    display: flex;
    justify-content: center;
    padding: 24px 0;
  }

  .emoji-ratings,
  .star-ratings {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .emoji-button,
  .star-button {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #9ca3af;
  }

  .emoji-button:hover:not(:disabled),
  .star-button:hover:not(:disabled) {
    border-color: #d1d5db;
    transform: scale(1.1);
  }

  .emoji-button.selected {
    border-color: var(--rating-color);
    background: var(--rating-color);
    color: white;
    transform: scale(1.15);
  }

  .star-button.selected {
    border-color: #fbbf24;
    transform: scale(1.1);
    color: #fbbf24;
  }

  .emoji-button:disabled,
  .star-button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
  }

  @media (max-width: 768px) {
    .emoji-ratings,
    .star-ratings {
      gap: 12px;
    }

    .emoji-button,
    .star-button {
      padding: 12px;
    }

    .emoji-button :global(svg),
    .star-button :global(svg) {
      width: 32px;
      height: 32px;
    }
  }
</style>
