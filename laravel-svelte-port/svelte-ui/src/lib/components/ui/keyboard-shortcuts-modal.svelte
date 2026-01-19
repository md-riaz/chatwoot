<script lang="ts">
  import * as Dialog from "$lib/components/ui/dialog";
  import { Keyboard } from "@lucide/svelte";
  import {
    KEYS,
    SHORTCUT_KEYS,
    SHORTCUT_TITLES,
    detectKeyboardLayout,
    needsShiftKeyForLayout,
  } from "$lib/keyboard/keyboard-shortcuts";

  let open = $state(false);
  let currentLayout = $state<string | null>(null);

  $effect(() => {
    if (typeof window === "undefined") {
      return;
    }

    const handleOpenKeyboardShortcuts = () => {
      open = true;
    };

    window.addEventListener("open-keyboard-shortcuts", handleOpenKeyboardShortcuts);

    return () => {
      window.removeEventListener("open-keyboard-shortcuts", handleOpenKeyboardShortcuts);
    };
  });

  $effect(() => {
    if (typeof window === "undefined") {
      return;
    }

    detectKeyboardLayout()
      .then((layout) => {
        currentLayout = layout;
      })
      .catch(() => {
        currentLayout = "QWERTY";
      });
  });
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="sm:max-w-xl">
    <Dialog.Header>
      <Dialog.Title class="flex items-center gap-2">
        <Keyboard class="h-5 w-5" />
        <span>Keyboard Shortcuts</span>
      </Dialog.Title>
      <Dialog.Description class="text-sm text-muted-foreground">
        View and learn all available keyboard shortcuts.
      </Dialog.Description>
    </Dialog.Header>

    <div class="grid gap-6 py-4">
      <div class="grid grid-cols-2 px-8 pt-0 pb-4 mt-2 gap-x-5 gap-y-3">
        <div class="flex justify-between items-center min-w-[25rem]">
          <p class="text-sm font-medium text-muted-foreground">
            View all shortcuts
          </p>
          <div class="flex items-center gap-2 mb-1 ml-2">
            <span
              class="inline-flex min-h-[28px] min-w-[60px] items-center justify-center rounded-md border-b-2 border-border bg-muted px-2.5 py-2 text-xs font-semibold"
            >
              {KEYS.WIN}
            </span>
            <span
              class="inline-flex min-h-[28px] min-w-[36px] items-center justify-center rounded-md border-b-2 border-border bg-muted px-2.5 py-2 text-xs font-semibold"
            >
              {KEYS.SLASH}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 px-8 pt-0 pb-4 gap-x-5 gap-y-3">
        {#each SHORTCUT_KEYS as shortcut (shortcut.id)}
          <div class="flex justify-between items-center min-w-[25rem]">
            <p class="text-sm text-muted-foreground min-w-[36px]">
              {SHORTCUT_TITLES[shortcut.label] ?? shortcut.label}
            </p>
            <div class="flex items-center gap-2 mb-1 ml-2">
              {#if needsShiftKeyForLayout(shortcut.keySet, currentLayout)}
                <span
                  class="inline-flex min-h-[28px] min-w-[36px] items-center justify-center rounded-md border-b-2 border-border bg-muted px-2.5 py-2 text-xs font-semibold"
                >
                  {KEYS.SHIFT}
                </span>
              {/if}

              {#each shortcut.displayKeys as key, index (index)}
                {#if key !== KEYS.SLASH}
                  <span
                    class="inline-flex min-h-[28px] min-w-[36px] items-center justify-center rounded-md border-b-2 border-border bg-muted px-2.5 py-2 text-xs font-semibold"
                  >
                    {key}
                  </span>
                {:else}
                  <span
                    class="flex items-center text-sm font-semibold text-muted-foreground"
                  >
                    {key}
                  </span>
                {/if}
              {/each}
            </div>
          </div>
        {/each}
      </div>
    </div>
  </Dialog.Content>
</Dialog.Root>
