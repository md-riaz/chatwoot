<script lang="ts">
  /**
   * SettingsSection - Settings section container
   */
  
  import type { Snippet } from 'svelte';
  import * as Card from '$lib/components/ui/card';
  
  interface Props {
    title: string;
    description?: string;
    children: Snippet;
  }
  
  let { title, description, children }: Props = $props();
</script>

<Card.Root>
  <Card.Header>
    <Card.Title>{title}</Card.Title>
    {#if description}
      <Card.Description>{description}</Card.Description>
    {/if}
  </Card.Header>
  <Card.Content>
    {@render children()}
  </Card.Content>
</Card.Root>
