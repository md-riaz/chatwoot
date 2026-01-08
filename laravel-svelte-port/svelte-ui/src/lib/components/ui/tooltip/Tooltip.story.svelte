<script lang="ts">
  import * as Tooltip from './index.js';
  import { Button } from '$lib/components/ui/button';
  
  export let Hst: any;
</script>

<Hst.Story title="Components/Tooltip" icon="lucide:message-square">
  <Hst.Variant title="Default">
    <div class="flex items-center justify-center p-16">
      <Tooltip.Provider>
        <Tooltip.Root>
          <Tooltip.Trigger>
            <Button variant="outline">Hover me</Button>
          </Tooltip.Trigger>
          <Tooltip.Content>
            <p>This is a tooltip</p>
          </Tooltip.Content>
        </Tooltip.Root>
      </Tooltip.Provider>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Positions">
    <div class="flex items-center justify-center gap-8 p-16">
      <Tooltip.Provider>
        <Tooltip.Root>
          <Tooltip.Trigger>
            <Button variant="outline" size="icon">↑</Button>
          </Tooltip.Trigger>
          <Tooltip.Content side="top">
            <p>Top</p>
          </Tooltip.Content>
        </Tooltip.Root>
      </Tooltip.Provider>
      
      <Tooltip.Provider>
        <Tooltip.Root>
          <Tooltip.Trigger>
            <Button variant="outline" size="icon">→</Button>
          </Tooltip.Trigger>
          <Tooltip.Content side="right">
            <p>Right</p>
          </Tooltip.Content>
        </Tooltip.Root>
      </Tooltip.Provider>
      
      <Tooltip.Provider>
        <Tooltip.Root>
          <Tooltip.Trigger>
            <Button variant="outline" size="icon">↓</Button>
          </Tooltip.Trigger>
          <Tooltip.Content side="bottom">
            <p>Bottom</p>
          </Tooltip.Content>
        </Tooltip.Root>
      </Tooltip.Provider>
      
      <Tooltip.Provider>
        <Tooltip.Root>
          <Tooltip.Trigger>
            <Button variant="outline" size="icon">←</Button>
          </Tooltip.Trigger>
          <Tooltip.Content side="left">
            <p>Left</p>
          </Tooltip.Content>
        </Tooltip.Root>
      </Tooltip.Provider>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Toolbar Example">
    <div class="flex items-center justify-center p-8">
      <div class="flex gap-1 rounded-lg border p-1">
        <Tooltip.Provider>
          <Tooltip.Root>
            <Tooltip.Trigger>
              <Button variant="ghost" size="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12"/><path d="M6 21h12"/><path d="m6 12 6-5v10l6-5"/></svg>
              </Button>
            </Tooltip.Trigger>
            <Tooltip.Content>Bold</Tooltip.Content>
          </Tooltip.Root>
        </Tooltip.Provider>
        
        <Tooltip.Provider>
          <Tooltip.Root>
            <Tooltip.Trigger>
              <Button variant="ghost" size="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
              </Button>
            </Tooltip.Trigger>
            <Tooltip.Content>Italic</Tooltip.Content>
          </Tooltip.Root>
        </Tooltip.Provider>
        
        <Tooltip.Provider>
          <Tooltip.Root>
            <Tooltip.Trigger>
              <Button variant="ghost" size="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4v16"/><path d="M18 4v16"/><path d="M6 12h12"/></svg>
              </Button>
            </Tooltip.Trigger>
            <Tooltip.Content>Heading</Tooltip.Content>
          </Tooltip.Root>
        </Tooltip.Provider>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
