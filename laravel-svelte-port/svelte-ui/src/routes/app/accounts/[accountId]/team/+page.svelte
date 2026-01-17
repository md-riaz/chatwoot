<script lang="ts">
	import { onMount } from 'svelte';
	import { Search, Mail, Shield, UserCheck, Edit, Trash2, UserPlus } from '@lucide/svelte';
	import * as Card from '$lib/components/ui/card';
	import * as Avatar from '$lib/components/ui/avatar';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Badge } from '$lib/components/ui/badge';
	import { Skeleton } from '$lib/components/ui/skeleton';

	// State for team members (placeholder - will be replaced with teamsStore)
	let teamMembers = $state([
		{
			id: 1,
			name: 'John Doe',
			email: 'john@example.com',
			role: 'Admin',
			availability: 'Online',
			avatar: null
		},
		{
			id: 2,
			name: 'Jane Smith',
			email: 'jane@example.com',
			role: 'Agent',
			availability: 'Online',
			avatar: null
		},
		{
			id: 3,
			name: 'Bob Johnson',
			email: 'bob@example.com',
			role: 'Agent',
			availability: 'Away',
			avatar: null
		},
		{
			id: 4,
			name: 'Alice Williams',
			email: 'alice@example.com',
			role: 'Viewer',
			availability: 'Offline',
			avatar: null
		}
	]);

	let searchQuery = $state('');
	let isLoading = $state(false);

	// Reactive filtered members
	const filteredMembers = $derived(() => {
		if (!searchQuery.trim()) return teamMembers;
		const query = searchQuery.toLowerCase();
		return teamMembers.filter(
			(member) =>
				member.name.toLowerCase().includes(query) || member.email.toLowerCase().includes(query)
		);
	});

	// Get initials from name
	function getInitials(name: string): string {
		return name
			.split(' ')
			.map((n) => n[0])
			.join('')
			.toUpperCase()
			.slice(0, 2);
	}

	// Get role badge variant
	function getRoleBadgeVariant(role: string): 'default' | 'secondary' | 'outline-solid' {
		switch (role) {
			case 'Admin':
				return 'default';
			case 'Agent':
				return 'secondary';
			default:
				return 'outline-solid';
		}
	}

	// Get role color class
	function getRoleColorClass(role: string): string {
		switch (role) {
			case 'Admin':
				return 'border-l-4 border-l-purple-500';
			case 'Agent':
				return 'border-l-4 border-l-blue-500';
			default:
				return 'border-l-4 border-l-gray-300';
		}
	}

	// Get availability badge variant
	function getAvailabilityVariant(availability: string): 'default' | 'secondary' | 'outline-solid' {
		switch (availability) {
			case 'Online':
				return 'default';
			case 'Away':
				return 'secondary';
			default:
				return 'outline-solid';
		}
	}

	// Get availability color
	function getAvailabilityColor(availability: string): string {
		switch (availability) {
			case 'Online':
				return 'bg-green-500';
			case 'Away':
				return 'bg-yellow-500';
			default:
				return 'bg-gray-400';
		}
	}

	onMount(() => {
		// TODO: Fetch team members from teamsStore
		// teamsStore.fetchTeamMembers();
	});
</script>

<div class="container mx-auto p-6">
	<!-- Header -->
	<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
		<div>
			<h1 class="text-3xl font-bold">Team Management</h1>
			<p class="text-muted-foreground">Manage your team members and their roles</p>
		</div>
		<Button>
			<UserPlus class="mr-2 h-4 w-4" />
			Add Team Member
		</Button>
	</div>

	<!-- Search -->
	<div class="mb-6">
		<div class="relative">
			<Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
			<Input
				type="text"
				placeholder="Search by name or email..."
				bind:value={searchQuery}
				class="pl-10"
			/>
		</div>
	</div>

	<!-- Team count -->
	<div class="mb-4">
		<p class="text-sm text-muted-foreground">
			{filteredMembers().length} {filteredMembers().length === 1 ? 'member' : 'members'}
		</p>
	</div>

	<!-- Loading state -->
	{#if isLoading}
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			{#each Array(6) as _}
				<Card.Root>
					<Card.Content class="p-6">
						<div class="flex items-start gap-4">
							<Skeleton class="h-12 w-12 rounded-full" />
							<div class="flex-1 space-y-2">
								<Skeleton class="h-4 w-32" />
								<Skeleton class="h-3 w-48" />
								<Skeleton class="h-6 w-20" />
							</div>
						</div>
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{:else if filteredMembers().length === 0}
		<!-- Empty state -->
		<Card.Root>
			<Card.Content class="flex flex-col items-center justify-center py-12">
				<UserCheck class="mb-4 h-12 w-12 text-muted-foreground" />
				<h3 class="mb-2 text-lg font-semibold">
					{searchQuery ? 'No team members found' : 'No team members yet'}
				</h3>
				<p class="mb-4 text-center text-sm text-muted-foreground">
					{searchQuery
						? 'Try adjusting your search query'
						: 'Get started by inviting team members to your workspace'}
				</p>
				{#if !searchQuery}
					<Button>
						<UserPlus class="mr-2 h-4 w-4" />
						Invite Team Member
					</Button>
				{/if}
			</Card.Content>
		</Card.Root>
	{:else}
		<!-- Team members grid -->
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			{#each filteredMembers() as member (member.id)}
				<Card.Root class="transition-shadow hover:shadow-md {getRoleColorClass(member.role)}">
					<Card.Content class="p-6">
						<div class="flex items-start gap-4">
							<!-- Avatar -->
							<Avatar.Root class="h-12 w-12">
								{#if member.avatar}
									<Avatar.Image src={member.avatar} alt={member.name} />
								{/if}
								<Avatar.Fallback>{getInitials(member.name)}</Avatar.Fallback>
							</Avatar.Root>

							<!-- Member info -->
							<div class="flex-1 min-w-0">
								<div class="mb-1 flex items-center gap-2">
									<h3 class="truncate font-semibold">{member.name}</h3>
									<div
										class="h-2 w-2 rounded-full {getAvailabilityColor(member.availability)}"
									></div>
								</div>
								<div class="mb-2 flex items-center gap-1 text-sm text-muted-foreground">
									<Mail class="h-3 w-3" />
									<a href="mailto:{member.email}" class="truncate hover:underline">
										{member.email}
									</a>
								</div>

								<!-- Role and status badges -->
								<div class="mb-3 flex flex-wrap gap-2">
									<Badge variant={getRoleBadgeVariant(member.role)} class="gap-1">
										<Shield class="h-3 w-3" />
										{member.role}
									</Badge>
									<Badge variant={getAvailabilityVariant(member.availability)}>
										{member.availability}
									</Badge>
								</div>

								<!-- Actions -->
								<div class="flex gap-2">
									<Button variant="outline" size="sm">
										<Edit class="mr-1 h-3 w-3" />
										Edit
									</Button>
									<Button variant="outline" size="sm">
										<Trash2 class="mr-1 h-3 w-3" />
										Remove
									</Button>
								</div>
							</div>
						</div>
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{/if}
</div>
