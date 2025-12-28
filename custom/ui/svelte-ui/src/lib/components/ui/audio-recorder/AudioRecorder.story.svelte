<script lang="ts">
  import { AudioRecorder } from './index.js';
  
  export let Hst: any;
  
  function handleRecordingComplete(blob: Blob) {
    console.log('Recording complete:', blob);
  }
  
  function handleRecordingStart() {
    console.log('Recording started');
  }
  
  function handleRecordingStop() {
    console.log('Recording stopped');
  }
</script>

<Hst.Story title="Media/AudioRecorder" icon="lucide:mic">
  <Hst.Variant title="Default">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-md">
        <AudioRecorder
          onRecordingComplete={handleRecordingComplete}
          onRecordingStart={handleRecordingStart}
          onRecordingStop={handleRecordingStop}
        />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="In a Message Form">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-2xl">
        <div class="rounded-lg border p-4">
          <h3 class="text-sm font-medium mb-4">Send Voice Message</h3>
          <AudioRecorder
            onRecordingComplete={handleRecordingComplete}
            onRecordingStart={handleRecordingStart}
            onRecordingStop={handleRecordingStop}
          />
          <div class="mt-4 flex justify-end gap-2">
            <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
              Cancel
            </button>
            <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
              Send
            </button>
          </div>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Compact View">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-sm">
        <AudioRecorder
          onRecordingComplete={handleRecordingComplete}
          onRecordingStart={handleRecordingStart}
          onRecordingStop={handleRecordingStop}
        />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="In Dialog">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-md">
        <div class="rounded-lg border bg-card p-6 shadow-sm">
          <div class="mb-4">
            <h3 class="text-lg font-semibold">Record Audio Note</h3>
            <p class="text-sm text-muted-foreground">
              Record a voice message for this conversation
            </p>
          </div>
          <AudioRecorder
            onRecordingComplete={handleRecordingComplete}
            onRecordingStart={handleRecordingStart}
            onRecordingStop={handleRecordingStop}
          />
        </div>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
