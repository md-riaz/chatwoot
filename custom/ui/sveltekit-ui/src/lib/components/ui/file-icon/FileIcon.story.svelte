<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { FileIcon } from './index';

  const fileTypes = [
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 
    'ppt', 'pptx', 'txt', 'csv', 'zip',
    'image', 'audio', 'video', 'unknown'
  ] as const;
</script>

<Hst.Story title="Visual/FileIcon" icon="lucide:file">
  <Hst.Variant title="All File Types">
    <div class="p-4 bg-background">
      <div class="grid grid-cols-4 gap-4">
        {#each fileTypes as type}
          <div class="flex flex-col items-center gap-2">
            <FileIcon {type} />
            <span class="text-xs text-muted-foreground">{type}</span>
          </div>
        {/each}
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sizes">
    <div class="flex items-center gap-4 p-4 bg-background">
      <div class="flex flex-col items-center gap-2">
        <FileIcon type="pdf" size="sm" />
        <span class="text-xs text-muted-foreground">Small</span>
      </div>
      <div class="flex flex-col items-center gap-2">
        <FileIcon type="pdf" size="md" />
        <span class="text-xs text-muted-foreground">Medium</span>
      </div>
      <div class="flex flex-col items-center gap-2">
        <FileIcon type="pdf" size="lg" />
        <span class="text-xs text-muted-foreground">Large</span>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Document Types">
    <div class="flex items-center gap-4 p-4 bg-background">
      <FileIcon type="pdf" size="lg" />
      <FileIcon type="doc" size="lg" />
      <FileIcon type="xls" size="lg" />
      <FileIcon type="ppt" size="lg" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Media Types">
    <div class="flex items-center gap-4 p-4 bg-background">
      <FileIcon type="image" size="lg" />
      <FileIcon type="audio" size="lg" />
      <FileIcon type="video" size="lg" />
    </div>
  </Hst.Variant>
</Hst.Story>
