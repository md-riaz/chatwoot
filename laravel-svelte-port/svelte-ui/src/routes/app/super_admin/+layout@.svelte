<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/state';
	import { Button } from '$lib/components/ui/button';
	import { ResponsiveSidebar } from '$lib/components/ui/sidebar';
	import { authStore } from '$lib/stores/auth.svelte';
	import {
	  AppWindow,
	  Activity,
	  Bot,
	  Building2,
	  ChevronDown,
	  ChevronRight,
	  Gauge,
	  LayoutDashboard,
	  LogOut,
	  Settings,
	  Users
	} from 'lucide-svelte';
	
	let { children } = $props();
	
	interface NavItem {
		label: string;
		href: string;
		icon: any;
		children?: NavItem[];
	}
	
	let expandedItems = $state(new Set<string>(['Settings'])); // Settings expanded by default
	
	const navItems: NavItem[] = [
		{ label: 'Dashboard', href: '/app/super_admin/dashboard', icon: LayoutDashboard },
		{ label: 'Accounts', href: '/app/super_admin/accounts', icon: Building2 },
		{ label: 'Users', href: '/app/super_admin/users', icon: Users },
		{ label: 'Agent Bots', href: '/app/super_admin/agent-bots', icon: Bot },
		{ label: 'Platform Apps', href: '/app/super_admin/platform-apps', icon: AppWindow },
		{ 
			label: 'Settings', 
			href: '/app/super_admin/settings', 
			icon: Settings,
			children: [
				{ label: 'General', href: '/app/super_admin/settings?config=general', icon: Settings },
				{ label: 'Email', href: '/app/super_admin/settings?config=email', icon: Settings },
				{ label: 'Messenger', href: '/app/super_admin/settings?config=messenger', icon: Settings },
				{ label: 'Instagram', href: '/app/super_admin/settings?config=instagram', icon: Settings },
				{ label: 'TikTok', href: '/app/super_admin/settings?config=tiktok', icon: Settings },
				{ label: 'Google', href: '/app/super_admin/settings?config=google', icon: Settings },
				{ label: 'Microsoft', href: '/app/super_admin/settings?config=microsoft', icon: Settings },
				{ label: 'Linear', href: '/app/super_admin/settings?config=linear', icon: Settings },
				{ label: 'Notion', href: '/app/super_admin/settings?config=notion', icon: Settings },
				{ label: 'Slack', href: '/app/super_admin/settings?config=slack', icon: Settings },
				{ label: 'WhatsApp Embedded', href: '/app/super_admin/settings?config=whatsapp_embedded', icon: Settings },
				{ label: 'Shopify', href: '/app/super_admin/settings?config=shopify', icon: Settings }
			]
		}
	];

	// Bottom navigation items (shown at bottom of sidebar)
	const bottomNavItems: NavItem[] = [
		{ label: 'Queue Horizon', href: '/horizon', icon: Gauge },
		{ label: 'Instance Health', href: '/app/super_admin/instance-health', icon: Activity },
		{ label: 'Agent Dashboard', href: '/', icon: LayoutDashboard }
	];
	
	function handleLogout() {
		authStore.logout();
		goto('/app/login');
	}
	
	function isActive(href: string): boolean {
		if (href.includes('?')) {
			// For settings with query params, check both path and query
			const [path, query] = href.split('?');
			return page.url.pathname === path && page.url.search.includes(query);
		}
		return page.url.pathname === href || page.url.pathname.startsWith(href + '/');
	}
	
	function toggleExpanded(label: string) {
		if (expandedItems.has(label)) {
			expandedItems.delete(label);
		} else {
			expandedItems.add(label);
		}
		expandedItems = new Set(expandedItems);
	}
</script>

<div class="flex h-screen overflow-hidden bg-background">
	<!-- Responsive Sidebar using shadcn-svelte components -->
	<ResponsiveSidebar mobileBreakpoint="lg">
		<!-- Logo -->
		<div class="p-6 border-b border-border shrink-0">
			<div class="flex items-center space-x-2">
				<div class="h-8 w-8 rounded bg-primary flex items-center justify-center">
					<span class="text-lg font-bold text-primary-foreground">C</span>
				</div>
				<div>
					<h1 class="text-lg font-semibold text-foreground">ClearLine</h1>
					<p class="text-xs text-muted-foreground">Super Admin</p>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		<nav class="flex-1 overflow-y-auto p-3">
			<div class="space-y-1">
				{#each navItems as item}
					{#if item.children}
						<!-- Expandable item -->
						<div>
							<button
								type="button"
								onclick={() => toggleExpanded(item.label)}
								class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-colors font-medium text-muted-foreground hover:bg-accent/50 hover:text-foreground"
							>
								<div class="flex items-center space-x-3">
									<item.icon class="h-5 w-5 flex-shrink-0" />
									<span>{item.label}</span>
								</div>
								{#if expandedItems.has(item.label)}
									<ChevronDown class="h-4 w-4" />
								{:else}
									<ChevronRight class="h-4 w-4" />
								{/if}
							</button>
							
							{#if expandedItems.has(item.label)}
								<div class="ml-6 mt-1 space-y-1">
									{#each item.children as child}
										<a
											href={child.href}
											class="flex items-center space-x-3 px-3 py-2 rounded-md text-sm transition-colors {isActive(child.href)
												? 'bg-accent text-accent-foreground'
												: 'text-muted-foreground hover:bg-accent/50 hover:text-foreground'}"
										>
											<span class="text-xs">•</span>
											<span>{child.label}</span>
										</a>
									{/each}
								</div>
							{/if}
						</div>
					{:else}
						<!-- Regular item -->
						<a
							href={item.href}
							class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-colors font-medium {isActive(item.href)
								? 'bg-accent text-accent-foreground'
								: 'text-muted-foreground hover:bg-accent/50 hover:text-foreground'}"
						>
							<item.icon class="h-5 w-5 flex-shrink-0" />
							<span>{item.label}</span>
						</a>
					{/if}
				{/each}
			</div>
		</nav>
		
		<!-- Bottom Navigation Items -->
		<div class="p-3 border-t border-border shrink-0">
			<div class="space-y-1">
				{#each bottomNavItems as item}
					<a
						href={item.href}
						class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-colors font-medium {isActive(item.href)
							? 'bg-accent text-accent-foreground'
							: 'text-muted-foreground hover:bg-accent/50 hover:text-foreground'}"
					>
						<item.icon class="h-5 w-5 flex-shrink-0" />
						<span>{item.label}</span>
					</a>
				{/each}
			</div>
		</div>
		
		<!-- User Info & Logout -->
		<div class="p-4 border-t border-border shrink-0">
			{#if authStore.currentUser.id}
				<div class="mb-3 px-2">
					<p class="text-sm font-medium truncate text-foreground">
						{authStore.currentUser.name}
					</p>
					<p class="text-xs truncate text-muted-foreground">
						{authStore.currentUser.email}
					</p>
				</div>
			{/if}
			<Button
				variant="outline"
				size="sm"
				class="w-full justify-start"
				onclick={handleLogout}
			>
				<LogOut class="h-4 w-4 mr-2" />
				Logout
			</Button>
		</div>
	</ResponsiveSidebar>
	
	<!-- Main Content -->
	<main class="flex-1 overflow-y-auto bg-background">
		<div class="lg:ml-0 ml-0">
			{@render children()}
		</div>
	</main>
</div>

<style>
	/* Custom hover effects */
	:global(.hover\:bg-accent\/50:hover) {
		background-color: hsl(var(--accent) / 0.5);
	}
</style>