<script lang="ts" module>
  export type AudioRecorderProps = {
    class?: string;
    onRecordingComplete?: (blob: Blob) => void;
    onRecordingStart?: () => void;
    onRecordingStop?: () => void;
  };
  
  export type RecordingState = 'idle' | 'recording' | 'paused' | 'stopped';
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '../button/index.js';
  
  let {
    class: className,
    onRecordingComplete,
    onRecordingStart,
    onRecordingStop,
    ...restProps
  }: AudioRecorderProps = $props();
  
  let recordingState = $state<RecordingState>('idle');
  let mediaRecorder: MediaRecorder | null = null;
  let audioChunks: Blob[] = [];
  let recordingDuration = $state<number>(0);
  let recordingInterval: ReturnType<typeof setInterval> | null = null;
  let audioUrl = $state<string | null>(null);
  let audioElement = $state<HTMLAudioElement | null>(null);
  let isPlaying = $state<boolean>(false);
  let error = $state<string | null>(null);
  
  async function startRecording() {
    try {
      error = null;
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      
      mediaRecorder = new MediaRecorder(stream);
      audioChunks = [];
      recordingDuration = 0;
      
      mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
          audioChunks.push(event.data);
        }
      };
      
      mediaRecorder.onstop = () => {
        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        audioUrl = URL.createObjectURL(audioBlob);
        
        if (onRecordingComplete) {
          onRecordingComplete(audioBlob);
        }
        
        // Stop all tracks
        stream.getTracks().forEach(track => track.stop());
      };
      
      mediaRecorder.start();
      recordingState = 'recording';
      
      recordingInterval = setInterval(() => {
        recordingDuration++;
      }, 1000);
      
      if (onRecordingStart) {
        onRecordingStart();
      }
    } catch (err) {
      error = 'Microphone access denied or not available';
      console.error('Error accessing microphone:', err);
    }
  }
  
  function stopRecording() {
    if (mediaRecorder && recordingState === 'recording') {
      mediaRecorder.stop();
      recordingState = 'stopped';
      
      if (recordingInterval) {
        clearInterval(recordingInterval);
        recordingInterval = null;
      }
      
      if (onRecordingStop) {
        onRecordingStop();
      }
    }
  }
  
  function cancelRecording() {
    if (mediaRecorder) {
      if (recordingState === 'recording') {
        mediaRecorder.stop();
      }
      
      if (recordingInterval) {
        clearInterval(recordingInterval);
        recordingInterval = null;
      }
    }
    
    recordingState = 'idle';
    recordingDuration = 0;
    audioUrl = null;
    audioChunks = [];
    isPlaying = false;
  }
  
  function togglePlayback() {
    if (!audioElement || !audioUrl) return;
    
    if (isPlaying) {
      audioElement.pause();
      isPlaying = false;
    } else {
      audioElement.play();
      isPlaying = true;
    }
  }
  
  function formatTime(seconds: number): string {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  }
  
  $effect(() => {
    return () => {
      if (recordingInterval) {
        clearInterval(recordingInterval);
      }
      if (audioUrl) {
        URL.revokeObjectURL(audioUrl);
      }
    };
  });
</script>

<div class={cn('w-full', className)} {...restProps}>
  <div class="rounded-lg border bg-card p-6">
    {#if error}
      <div class="mb-4 rounded-md bg-destructive/10 p-3 text-sm text-destructive">
        {error}
      </div>
    {/if}
    
    {#if recordingState === 'idle'}
      <!-- Ready to Record -->
      <div class="flex flex-col items-center gap-4">
        <div class="flex size-20 items-center justify-center rounded-full bg-primary/10">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="text-primary"
          >
            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
            <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
            <line x1="12" y1="19" x2="12" y2="22" />
          </svg>
        </div>
        
        <p class="text-sm text-muted-foreground">
          Ready to record
        </p>
        
        <Button onclick={startRecording}>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="mr-2"
          >
            <circle cx="12" cy="12" r="10" />
          </svg>
          Start Recording
        </Button>
      </div>
    {:else if recordingState === 'recording'}
      <!-- Recording -->
      <div class="flex flex-col items-center gap-4">
        <div class="flex size-20 items-center justify-center rounded-full bg-destructive/10 animate-pulse">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="text-destructive"
          >
            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
            <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
            <line x1="12" y1="19" x2="12" y2="22" />
          </svg>
        </div>
        
        <div class="text-center">
          <p class="text-2xl font-mono font-bold">
            {formatTime(recordingDuration)}
          </p>
          <p class="text-sm text-muted-foreground">
            Recording...
          </p>
        </div>
        
        <div class="flex gap-2">
          <Button variant="outline" onclick={cancelRecording}>
            Cancel
          </Button>
          <Button variant="destructive" onclick={stopRecording}>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="mr-2"
            >
              <rect x="6" y="6" width="12" height="12" rx="2" />
            </svg>
            Stop
          </Button>
        </div>
      </div>
    {:else if recordingState === 'stopped'}
      <!-- Playback -->
      <div class="space-y-4">
        <div class="flex items-center gap-4">
          <button
            type="button"
            class="flex size-12 items-center justify-center rounded-full bg-primary text-primary-foreground hover:bg-primary/90"
            onclick={togglePlayback}
          >
            {#if isPlaying}
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <rect x="6" y="4" width="4" height="16" />
                <rect x="14" y="4" width="4" height="16" />
              </svg>
            {:else}
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <polygon points="5 3 19 12 5 21 5 3" />
              </svg>
            {/if}
          </button>
          
          <div class="flex-1">
            <p class="text-sm font-medium">
              Recording ({formatTime(recordingDuration)})
            </p>
            <p class="text-xs text-muted-foreground">
              Click play to listen
            </p>
          </div>
          
          <Button variant="outline" size="sm" onclick={cancelRecording}>
            New Recording
          </Button>
        </div>
        
        {#if audioUrl}
          <audio
            bind:this={audioElement}
            src={audioUrl}
            onended={() => { isPlaying = false; }}
            class="w-full"
            controls
          ></audio>
        {/if}
      </div>
    {/if}
  </div>
</div>
