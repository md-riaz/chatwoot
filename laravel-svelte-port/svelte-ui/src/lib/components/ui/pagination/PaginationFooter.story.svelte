<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { PaginationFooter } from './index';

  let defaultPage = $state(1);
  let middlePage = $state(3);
  let lastPage = $state(7);
  let customPage = $state(2);
  let singlePage = $state(1);
</script>

<Hst.Story title="Navigation/PaginationFooter" icon="lucide:arrow-left-right">
  <Hst.Variant title="Default (Page 1)">
    <div class="p-4 bg-background">
      <PaginationFooter
        currentPage={defaultPage}
        totalItems={100}
        itemsPerPage={16}
        onPageChange={(page) => defaultPage = page}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Middle Page">
    <div class="p-4 bg-background">
      <PaginationFooter
        currentPage={middlePage}
        totalItems={100}
        itemsPerPage={16}
        onPageChange={(page) => middlePage = page}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Last Page">
    <div class="p-4 bg-background">
      <PaginationFooter
        currentPage={lastPage}
        totalItems={100}
        itemsPerPage={16}
        onPageChange={(page) => lastPage = page}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Items Per Page (10)">
    <div class="p-4 bg-background">
      <PaginationFooter
        currentPage={customPage}
        totalItems={100}
        itemsPerPage={10}
        onPageChange={(page) => customPage = page}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Single Page">
    <div class="p-4 bg-background">
      <PaginationFooter
        currentPage={singlePage}
        totalItems={10}
        itemsPerPage={16}
        onPageChange={(page) => singlePage = page}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
