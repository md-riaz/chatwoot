<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Textarea } from '$lib/components/ui/textarea';

  interface Props {
    open?: boolean;
    onClose?: () => void;
    onSubmit?: (rule: { name: string; description: string; conditions: string }) => void;
    class?: string;
  }

  let { open = false, onClose, onSubmit, class: className }: Props = $props();
  let name = $state('');
  let description = $state('');
  let conditions = $state('');

  function handleSubmit() {
    onSubmit?.({ name, description, conditions });
    name = '';
    description = '';
    conditions = '';
  }
</script>

{#if open}
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <button class="absolute inset-0 bg-black/50" onclick={onClose} aria-label="Close" />
    <div class={cn('relative z-10 w-full max-w-lg bg-white dark:bg-slate-900 rounded-lg shadow-xl p-6', className)}>
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Add New Rule</h2>
        <Button variant="ghost" size="icon" onclick={onClose}>
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </Button>
      </div>
      
      <div class="space-y-4">
        <div>
          <label for="rule-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rule Name</label>
          <Input id="rule-name" bind:value={name} placeholder="Enter rule name" />
        </div>
        <div>
          <label for="rule-desc" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
          <Input id="rule-desc" bind:value={description} placeholder="Describe the rule" />
        </div>
        <div>
          <label for="rule-conditions" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Conditions</label>
          <Textarea id="rule-conditions" bind:value={conditions} placeholder="Define conditions" rows={3} />
        </div>
      </div>

      <div class="flex justify-end gap-2 mt-6">
        <Button variant="outline" onclick={onClose}>Cancel</Button>
        <Button onclick={handleSubmit}>Add Rule</Button>
      </div>
    </div>
  </div>
{/if}
