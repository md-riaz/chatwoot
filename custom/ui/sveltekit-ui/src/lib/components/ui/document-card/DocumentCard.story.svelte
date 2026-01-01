<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { DocumentCard } from './index';

  const documents = [
    {
      id: 1,
      name: 'Product Documentation.pdf',
      type: 'pdf',
      size: '2.4 MB',
      status: 'completed' as const,
      uploadedAt: '2024-12-15T10:00:00Z',
    },
    {
      id: 2,
      name: 'FAQ Database.xlsx',
      type: 'xlsx',
      size: '512 KB',
      status: 'processing' as const,
      uploadedAt: '2024-12-20T14:30:00Z',
    },
    {
      id: 3,
      name: 'Training Material.docx',
      type: 'docx',
      size: '1.8 MB',
      status: 'failed' as const,
      uploadedAt: '2024-12-18T09:00:00Z',
    },
  ];
</script>

<Hst.Story title="Captain/DocumentCard" icon="lucide:file-text">
  <Hst.Variant title="Document List">
    <div class="flex flex-col gap-4 p-4 bg-background max-w-2xl">
      {#each documents as doc}
        <DocumentCard
          id={doc.id}
          name={doc.name}
          type={doc.type}
          size={doc.size}
          status={doc.status}
          uploadedAt={doc.uploadedAt}
          onclick={() => console.log('Clicked document:', doc.id)}
        />
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Completed Document">
    <div class="p-4 bg-background max-w-2xl">
      <DocumentCard
        id={1}
        name="Knowledge Base Export.pdf"
        type="pdf"
        size="5.2 MB"
        status="completed"
        uploadedAt="2024-12-20T10:00:00Z"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Processing Document">
    <div class="p-4 bg-background max-w-2xl">
      <DocumentCard
        id={2}
        name="New Training Data.csv"
        type="csv"
        size="128 KB"
        status="processing"
        uploadedAt="2024-12-25T15:00:00Z"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Failed Document">
    <div class="p-4 bg-background max-w-2xl">
      <DocumentCard
        id={3}
        name="Corrupted File.zip"
        type="zip"
        size="10 MB"
        status="failed"
        uploadedAt="2024-12-22T08:00:00Z"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
