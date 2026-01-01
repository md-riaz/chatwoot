<script lang="ts">
  import { FileUpload } from './index.js';
  
  export let Hst: any;
  
  function handleFilesSelected(files: File[]) {
    console.log('Files selected:', files);
  }
  
  function handleFileRemoved(file: File) {
    console.log('File removed:', file);
  }
</script>

<Hst.Story title="Media/FileUpload" icon="lucide:upload">
  <Hst.Variant title="Default">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <FileUpload onFilesSelected={handleFilesSelected} onFilesRemoved={handleFileRemoved} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Multiple Files">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <FileUpload
          multiple
          maxFiles={5}
          onFilesSelected={handleFilesSelected}
          onFilesRemoved={handleFileRemoved}
        />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Images Only">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <FileUpload
          accept="image/*"
          multiple
          onFilesSelected={handleFilesSelected}
          onFilesRemoved={handleFileRemoved}
        />
        <p class="mt-2 text-sm text-muted-foreground">
          Accepts: image files only
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Documents Only">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <FileUpload
          accept=".pdf,.doc,.docx,.txt"
          multiple
          onFilesSelected={handleFilesSelected}
          onFilesRemoved={handleFileRemoved}
        />
        <p class="mt-2 text-sm text-muted-foreground">
          Accepts: PDF, DOC, DOCX, TXT
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Max Size">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <FileUpload
          maxSize={2 * 1024 * 1024}
          multiple
          onFilesSelected={handleFilesSelected}
          onFilesRemoved={handleFileRemoved}
        />
        <p class="mt-2 text-sm text-muted-foreground">
          Maximum file size: 2 MB
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled State">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <FileUpload disabled />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="In a Form">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <form class="space-y-4">
          <div>
            <label class="text-sm font-medium">Attach Files</label>
            <div class="mt-2">
              <FileUpload
                multiple
                maxFiles={3}
                onFilesSelected={handleFilesSelected}
                onFilesRemoved={handleFileRemoved}
              />
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
              Upload up to 3 files
            </p>
          </div>
          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
          >
            Submit
          </button>
        </form>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Compact View">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-md">
        <FileUpload
          multiple
          onFilesSelected={handleFilesSelected}
          onFilesRemoved={handleFileRemoved}
        />
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
