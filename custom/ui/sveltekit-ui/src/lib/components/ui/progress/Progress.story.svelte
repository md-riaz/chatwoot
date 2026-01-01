<script lang="ts">
  import { Progress } from './index.js';
  
  export let Hst: any;
  
  let progress = $state(33);
  let animatedProgress = $state(0);
  
  // Simulate loading
  $effect(() => {
    const timer = setInterval(() => {
      animatedProgress = animatedProgress >= 100 ? 0 : animatedProgress + 10;
    }, 500);
    return () => clearInterval(timer);
  });
</script>

<Hst.Story title="Components/Progress" icon="lucide:loader">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-md space-y-4">
      <Progress value={33} />
      <p class="text-sm text-muted-foreground">33% complete</p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Different Values">
    <div class="p-4 max-w-md space-y-6">
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span>0%</span>
          <span>Empty</span>
        </div>
        <Progress value={0} />
      </div>
      
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span>25%</span>
          <span>Quarter</span>
        </div>
        <Progress value={25} />
      </div>
      
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span>50%</span>
          <span>Half</span>
        </div>
        <Progress value={50} />
      </div>
      
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span>75%</span>
          <span>Three quarters</span>
        </div>
        <Progress value={75} />
      </div>
      
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span>100%</span>
          <span>Complete</span>
        </div>
        <Progress value={100} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Animated">
    <div class="p-4 max-w-md space-y-2">
      <Progress value={animatedProgress} />
      <p class="text-sm text-muted-foreground text-center">{animatedProgress}%</p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Colors">
    <div class="p-4 max-w-md space-y-4">
      <div class="space-y-2">
        <span class="text-sm">Primary (default)</span>
        <Progress value={66} />
      </div>
      <div class="space-y-2">
        <span class="text-sm">Success</span>
        <Progress value={66} class="[&>div]:bg-success" />
      </div>
      <div class="space-y-2">
        <span class="text-sm">Warning</span>
        <Progress value={66} class="[&>div]:bg-warning" />
      </div>
      <div class="space-y-2">
        <span class="text-sm">Destructive</span>
        <Progress value={66} class="[&>div]:bg-destructive" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="File Upload Example">
    <div class="p-4 max-w-md">
      <div class="border rounded-lg p-4 space-y-3">
        <div class="flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">document.pdf</p>
            <p class="text-xs text-muted-foreground">2.5 MB</p>
          </div>
          <span class="text-sm text-muted-foreground">{animatedProgress}%</span>
        </div>
        <Progress value={animatedProgress} class="h-2" />
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
