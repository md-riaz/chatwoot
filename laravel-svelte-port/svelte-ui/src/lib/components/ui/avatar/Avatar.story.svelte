<script lang="ts">
  import * as Avatar from './index.js';
  
  export let Hst: any;
  
  const sizes = ['sm', 'default', 'lg', 'xl'] as const;
  
  const users = [
    { name: 'John Doe', initials: 'JD', image: 'https://api.dicebear.com/7.x/avataaars/svg?seed=John' },
    { name: 'Jane Smith', initials: 'JS', image: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Jane' },
    { name: 'Bob Wilson', initials: 'BW', image: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Bob' },
    { name: 'Alice Brown', initials: 'AB', image: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Alice' }
  ];
</script>

<Hst.Story title="Primitives/Avatar" icon="lucide:user-circle">
  <Hst.Variant title="Default">
    <div class="flex flex-wrap gap-4 p-4">
      <Avatar.Root>
        <Avatar.Image src={users[0].image} alt={users[0].name} />
        <Avatar.Fallback>{users[0].initials}</Avatar.Fallback>
      </Avatar.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sizes">
    <div class="flex flex-wrap items-center gap-4 p-4">
      {#each sizes as size}
        <div class="text-center">
          <Avatar.Root {size}>
            <Avatar.Image src={users[0].image} alt={users[0].name} />
            <Avatar.Fallback>{users[0].initials}</Avatar.Fallback>
          </Avatar.Root>
          <p class="text-xs text-muted-foreground mt-1">{size}</p>
        </div>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Fallback">
    <div class="flex flex-wrap gap-4 p-4">
      <Avatar.Root>
        <Avatar.Image src="" alt="No image" />
        <Avatar.Fallback>JD</Avatar.Fallback>
      </Avatar.Root>
      <Avatar.Root>
        <Avatar.Image src="invalid-url" alt="Invalid" />
        <Avatar.Fallback>AB</Avatar.Fallback>
      </Avatar.Root>
      <Avatar.Root>
        <Avatar.Fallback class="bg-primary text-primary-foreground">CW</Avatar.Fallback>
      </Avatar.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="User List">
    <div class="flex flex-wrap gap-4 p-4">
      {#each users as user}
        <div class="flex items-center gap-2">
          <Avatar.Root>
            <Avatar.Image src={user.image} alt={user.name} />
            <Avatar.Fallback>{user.initials}</Avatar.Fallback>
          </Avatar.Root>
          <span class="text-sm font-medium">{user.name}</span>
        </div>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Avatar Stack">
    <div class="flex p-4">
      <div class="flex -space-x-3">
        {#each users as user, i}
          <Avatar.Root class="border-2 border-background">
            <Avatar.Image src={user.image} alt={user.name} />
            <Avatar.Fallback>{user.initials}</Avatar.Fallback>
          </Avatar.Root>
        {/each}
        <Avatar.Root class="border-2 border-background">
          <Avatar.Fallback class="bg-muted text-muted-foreground text-xs">+5</Avatar.Fallback>
        </Avatar.Root>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Status Indicator">
    <div class="flex flex-wrap gap-6 p-4">
      <div class="relative">
        <Avatar.Root>
          <Avatar.Image src={users[0].image} alt={users[0].name} />
          <Avatar.Fallback>{users[0].initials}</Avatar.Fallback>
        </Avatar.Root>
        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 ring-2 ring-background"></span>
      </div>
      <div class="relative">
        <Avatar.Root>
          <Avatar.Image src={users[1].image} alt={users[1].name} />
          <Avatar.Fallback>{users[1].initials}</Avatar.Fallback>
        </Avatar.Root>
        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-yellow-500 ring-2 ring-background"></span>
      </div>
      <div class="relative">
        <Avatar.Root>
          <Avatar.Image src={users[2].image} alt={users[2].name} />
          <Avatar.Fallback>{users[2].initials}</Avatar.Fallback>
        </Avatar.Root>
        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-red-500 ring-2 ring-background"></span>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
