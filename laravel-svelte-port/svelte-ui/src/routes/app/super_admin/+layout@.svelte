<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/state';
	import { Button } from '$lib/components/ui/button';
	import * as Sidebar from '$lib/components/ui/sidebar';
	import * as Collapsible from '$lib/components/ui/collapsible';
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
	
	let expandedItems = $state(new Set<string>(['/app/super_admin/settings'])); // Settings expanded by default
	
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
	
	function toggleExpanded(href: string) {
		if (expandedItems.has(href)) {
			expandedItems.delete(href);
		} else {
			expandedItems.add(href);
		}
		expandedItems = new Set(expandedItems);
	}
</script>

<Sidebar.Provider>
	<Sidebar.Root variant="inset" collapsible="offcanvas">
		<Sidebar.Header>
			<!-- Logo -->
			<div class="flex items-center space-x-2 px-2">
				<div class="h-8 w-8 rounded bg-primary flex items-center justify-center">
					<span class="text-lg font-bold text-primary-foreground">C</span>
				</div>
				<div>
					<h1 class="text-lg font-semibold text-foreground">ClearLine</h1>
					<p class="text-xs text-muted-foreground">Super Admin</p>
				</div>
			</div>
		</Sidebar.Header>
		
		<Sidebar.Content>
			<Sidebar.Group>
				<Sidebar.GroupContent>
					<Sidebar.Menu>
						{#each navItems as item}
							{#if item.children}
								<!-- Collapsible menu item with children -->
								<Collapsible.Root open={expandedItems.has(item.href)} class="group/collapsible">
									<Sidebar.MenuItem>
										<Collapsible.Trigger>
											{#snippet child({ props })}
												<Sidebar.MenuButton
													{...props}
													onclick={() => toggleExpanded(item.href)}
												>
													<item.icon />
													<span>{item.label}</span>
													{#if expandedItems.has(item.href)}
														<ChevronDown class="ml-auto h-4 w-4 transition-transform" />
													{:else}
														<ChevronRight class="ml-auto h-4 w-4 transition-transform" />
													{/if}
												</Sidebar.MenuButton>
											{/snippet}
										</Collapsible.Trigger>
										<Collapsible.Content>
											<Sidebar.MenuSub>
												{#each item.children as child}
													<Sidebar.MenuSubItem>
														<Sidebar.MenuSubButton
															href={child.href}
															isActive={isActive(child.href)}
														>
															<span class="text-xs">•</span>
															<span>{child.label}</span>
														</Sidebar.MenuSubButton>
													</Sidebar.MenuSubItem>
												{/each}
											</Sidebar.MenuSub>
										</Collapsible.Content>
									</Sidebar.MenuItem>
								</Collapsible.Root>
							{:else}
								<!-- Regular menu item -->
								<Sidebar.MenuItem>
									<Sidebar.MenuButton isActive={isActive(item.href)}>
										{#snippet child({ props })}
											<a href={item.href} {...props}>
												<item.icon />
												<span>{item.label}</span>
											</a>
										{/snippet}
									</Sidebar.MenuButton>
								</Sidebar.MenuItem>
							{/if}
						{/each}
					</Sidebar.Menu>
				</Sidebar.GroupContent>
			</Sidebar.Group>
			
			<Sidebar.Separator />
			
			<!-- Bottom navigation items -->
			<Sidebar.Group>
				<Sidebar.GroupContent>
					<Sidebar.Menu>
						{#each bottomNavItems as item}
							<Sidebar.MenuItem>
								<Sidebar.MenuButton isActive={isActive(item.href)}>
									{#snippet child({ props })}
										<a href={item.href} {...props}>
											<item.icon />
											<span>{item.label}</span>
										</a>
									{/snippet}
								</Sidebar.MenuButton>
							</Sidebar.MenuItem>
						{/each}
					</Sidebar.Menu>
				</Sidebar.GroupContent>
			</Sidebar.Group>
		</Sidebar.Content>
		
		<Sidebar.Footer>
			<!-- User info & logout -->
			{#if authStore.currentUser.id}
				<div class="mb-2 px-2">
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
		</Sidebar.Footer>
		
		<Sidebar.Rail />
	</Sidebar.Root>
	
	<Sidebar.Inset>
		<header class="flex h-16 shrink-0 items-center gap-2 px-4 border-b sticky top-0 bg-background z-10">
			<Sidebar.Trigger class="-ms-1" />
			<h1 class="text-sm font-medium text-muted-foreground">
				Super Admin
			</h1>
		</header>
		<main class="flex-1 p-4">
			{@render children()}
		</main>
	</Sidebar.Inset>
</Sidebar.Provider>
