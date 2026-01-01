<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';
	import { authStore } from '$lib/stores/auth';
	import { Button } from '$lib/components/ui/button';
	import { Separator } from '$lib/components/ui/separator';
	import {
		LayoutDashboard,
		Users,
		Building2,
		Settings,
		Bot,
		AppWindow,
		KeyRound,
		Database,
		UserCog,
		ScrollText,
		HardDrive,
		LogOut
	} from 'lucide-svelte';
	
	let { children } = $props();
	
	interface NavItem {
		label: string;
		href: string;
		icon: any;
	}
	
	const navItems: NavItem[] = [
		{ label: 'Dashboard', href: '/app/super_admin/dashboard', icon: LayoutDashboard },
		{ label: 'Accounts', href: '/app/super_admin/accounts', icon: Building2 },
		{ label: 'Users', href: '/app/super_admin/users', icon: Users },
		{ label: 'Settings', href: '/app/super_admin/settings', icon: Settings },
		{ label: 'Agent Bots', href: '/app/super_admin/agent-bots', icon: Bot },
		{ label: 'Platform Apps', href: '/app/super_admin/platform-apps', icon: AppWindow },
		{ label: 'Access Tokens', href: '/app/super_admin/access-tokens', icon: KeyRound },
		{ label: 'Installation Configs', href: '/app/super_admin/installation-configs', icon: Database },
		{ label: 'Account Users', href: '/app/super_admin/account-users', icon: UserCog },
		{ label: 'Audit Logs', href: '/app/super_admin/audit-logs', icon: ScrollText },
		{ label: 'Cache', href: '/app/super_admin/cache', icon: HardDrive }
	];
	
	function handleLogout() {
		authStore.logout();
		goto('/login');
	}
	
	function isActive(href: string): boolean {
		return $page.url.pathname === href || $page.url.pathname.startsWith(href + '/');
	}
</script>

<div class="flex h-screen overflow-hidden">
	<!-- Sidebar -->
	<aside class="w-64 border-r bg-card flex flex-col">
		<!-- Logo -->
		<div class="p-6 border-b">
			<div class="flex items-center space-x-2">
				<div class="h-8 w-8 rounded bg-primary flex items-center justify-center">
					<span class="text-lg font-bold text-primary-foreground">C</span>
				</div>
				<div>
					<h1 class="text-lg font-semibold">Chatwoot</h1>
					<p class="text-xs text-muted-foreground">Super Admin</p>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		<nav class="flex-1 overflow-y-auto p-4">
			<div class="space-y-1">
				{#each navItems as item}
					<a
						href={item.href}
						class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm transition-colors {isActive(item.href)
							? 'bg-primary text-primary-foreground font-medium'
							: 'text-muted-foreground hover:bg-muted hover:text-foreground'}"
					>
						<svelte:component this={item.icon} class="h-5 w-5" />
						<span>{item.label}</span>
					</a>
				{/each}
			</div>
		</nav>
		
		<!-- User Info & Logout -->
		<div class="p-4 border-t">
			{#if $authStore.user}
				<div class="mb-3">
					<p class="text-sm font-medium">{$authStore.user.name}</p>
					<p class="text-xs text-muted-foreground">{$authStore.user.email}</p>
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
	</aside>
	
	<!-- Main Content -->
	<main class="flex-1 overflow-y-auto">
		{@render children()}
	</main>
</div>
