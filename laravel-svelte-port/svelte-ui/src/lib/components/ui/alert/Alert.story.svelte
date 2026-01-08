<script lang="ts">
  import * as Alert from './index.js';
  
  export let Hst: any;
  
  const variants = ['default', 'destructive', 'success', 'warning', 'info'] as const;
</script>

<Hst.Story title="Components/Alert" icon="lucide:alert-circle">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-md">
      <Alert.Root>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <Alert.Title>Heads up!</Alert.Title>
        <Alert.Description>
          You can add components and dependencies to your app using the cli.
        </Alert.Description>
      </Alert.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Variants">
    <div class="p-4 space-y-4 max-w-md">
      <Alert.Root>
        <Alert.Title>Default</Alert.Title>
        <Alert.Description>This is a default alert message.</Alert.Description>
      </Alert.Root>
      
      <Alert.Root variant="destructive">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <Alert.Title>Error</Alert.Title>
        <Alert.Description>Something went wrong. Please try again.</Alert.Description>
      </Alert.Root>
      
      <Alert.Root variant="success">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
        <Alert.Title>Success</Alert.Title>
        <Alert.Description>Your changes have been saved successfully.</Alert.Description>
      </Alert.Root>
      
      <Alert.Root variant="warning">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <Alert.Title>Warning</Alert.Title>
        <Alert.Description>Your session is about to expire.</Alert.Description>
      </Alert.Root>
      
      <Alert.Root variant="info">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <Alert.Title>Info</Alert.Title>
        <Alert.Description>A new version is available for download.</Alert.Description>
      </Alert.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Without Icon">
    <div class="p-4 space-y-4 max-w-md">
      <Alert.Root>
        <Alert.Title>Note</Alert.Title>
        <Alert.Description>
          This is an alert without an icon. It's useful for less prominent messages.
        </Alert.Description>
      </Alert.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
