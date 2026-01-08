<script lang="ts">
  import { EmojiPicker } from './index.js';
  import { Popover } from '../popover/index.js';
  import { Button } from '../button/index.js';
  
  export let Hst: any;
  
  let selectedEmoji = $state('');
  let messageText = $state('');
  
  function handleEmojiSelect(emoji: string) {
    console.log('Emoji selected:', emoji);
    selectedEmoji = emoji;
  }
  
  function addEmojiToMessage(emoji: string) {
    messageText += emoji;
  }
</script>

<Hst.Story title="Media/EmojiPicker" icon="lucide:smile">
  <Hst.Variant title="Default">
    <div class="flex justify-center p-4">
      <EmojiPicker onEmojiSelect={handleEmojiSelect} />
    </div>
    {#if selectedEmoji}
      <div class="text-center mt-4">
        <p class="text-sm text-muted-foreground">Last selected: {selectedEmoji}</p>
      </div>
    {/if}
  </Hst.Variant>

  <Hst.Variant title="In Popover">
    <div class="flex justify-center p-4">
      <Popover>
        <Popover.Trigger asChild let:builder>
          <Button variant="outline" builders={[builder]}>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="mr-2"
            >
              <circle cx="12" cy="12" r="10" />
              <path d="M8 14s1.5 2 4 2 4-2 4-2" />
              <line x1="9" y1="9" x2="9.01" y2="9" />
              <line x1="15" y1="9" x2="15.01" y2="9" />
            </svg>
            Add Emoji
          </Button>
        </Popover.Trigger>
        <Popover.Content class="w-auto p-0">
          <EmojiPicker onEmojiSelect={handleEmojiSelect} />
        </Popover.Content>
      </Popover>
      {#if selectedEmoji}
        <span class="ml-4 text-2xl">{selectedEmoji}</span>
      {/if}
    </div>
  </Hst.Variant>

  <Hst.Variant title="In Message Composer">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <div class="rounded-lg border p-4">
          <div class="mb-2">
            <textarea
              bind:value={messageText}
              placeholder="Type your message..."
              class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            />
          </div>
          <div class="flex items-center justify-between">
            <Popover>
              <Popover.Trigger asChild let:builder>
                <Button variant="ghost" size="sm" builders={[builder]}>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <circle cx="12" cy="12" r="10" />
                    <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                    <line x1="9" y1="9" x2="9.01" y2="9" />
                    <line x1="15" y1="9" x2="15.01" y2="9" />
                  </svg>
                </Button>
              </Popover.Trigger>
              <Popover.Content class="w-auto p-0" align="start">
                <EmojiPicker onEmojiSelect={addEmojiToMessage} />
              </Popover.Content>
            </Popover>
            <Button>Send</Button>
          </div>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Compact View">
    <div class="flex justify-center p-4">
      <div class="w-80">
        <EmojiPicker onEmojiSelect={handleEmojiSelect} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Recent Emojis">
    <div class="flex justify-center p-4">
      <EmojiPicker onEmojiSelect={handleEmojiSelect} />
      <div class="ml-4">
        <p class="text-sm text-muted-foreground mb-2">
          Click some emojis to see them in "Recent"
        </p>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
