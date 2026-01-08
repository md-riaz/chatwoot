<script lang="ts">
  import { Badge } from './index.js';
  
  export let Hst: any;
  
  const variants = ['default', 'secondary', 'destructive', 'outline', 'success', 'warning', 'info'] as const;
</script>

<Hst.Story title="Primitives/Badge" icon="lucide:tag">
  <Hst.Variant title="Default">
    <div class="flex flex-wrap gap-4 p-4">
      <Badge>Badge</Badge>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Variants">
    <div class="flex flex-wrap gap-2 p-4">
      {#each variants as variant}
        <Badge {variant}>{variant}</Badge>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Status Badges">
    <div class="flex flex-wrap gap-2 p-4">
      <Badge variant="success">Online</Badge>
      <Badge variant="warning">Away</Badge>
      <Badge variant="destructive">Offline</Badge>
      <Badge variant="info">Busy</Badge>
      <Badge variant="secondary">Unknown</Badge>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Icons">
    <div class="flex flex-wrap gap-2 p-4">
      <Badge>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        Pending
      </Badge>
      <Badge variant="success">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
        Completed
      </Badge>
      <Badge variant="destructive">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        Failed
      </Badge>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation Status">
    <div class="flex flex-wrap gap-2 p-4">
      <Badge variant="success">Open</Badge>
      <Badge variant="default">Resolved</Badge>
      <Badge variant="warning">Snoozed</Badge>
      <Badge variant="destructive">Pending</Badge>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Priority Levels">
    <div class="flex flex-wrap gap-2 p-4">
      <Badge variant="outline">None</Badge>
      <Badge variant="info">Low</Badge>
      <Badge variant="warning">Medium</Badge>
      <Badge variant="destructive">High</Badge>
      <Badge class="bg-purple-500 text-white border-transparent">Urgent</Badge>
    </div>
  </Hst.Variant>
</Hst.Story>
