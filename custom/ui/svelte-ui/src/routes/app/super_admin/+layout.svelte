<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';
	import { authStore } from '$lib/stores/auth.svelte';
	import { Button } from '$lib/components/ui/button';
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
		goto('/app/login');
	}
	
	function isActive(href: string): boolean {
		return $page.url.pathname === href || $page.url.pathname.startsWith(href + '/');
	}
</script>

<div class="flex h-screen overflow-hidden bg-background-color">
	<!-- Sidebar matching Vue/Chatwoot design -->
	<aside class="w-64 border-r flex flex-col bg-white dark:bg-slate-1" style="border-color: rgb(var(--slate-6));">
		<!-- Logo -->
		<div class="p-6 border-b" style="border-color: rgb(var(--slate-6));">
			<div class="flex items-center space-x-2">
				<div class="h-8 w-8 rounded bg-iris-9 flex items-center justify-center" style="background-color: rgb(var(--iris-9));">
					<span class="text-lg font-bold text-white">C</span>
				</div>
				<div>
					<h1 class="text-lg font-semibold" style="color: rgb(var(--slate-12));">Chatwoot</h1>
					<p class="text-xs" style="color: rgb(var(--slate-11));">Super Admin</p>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		<nav class="flex-1 overflow-y-auto p-3">
			<div class="space-y-1">
				{#each navItems as item}
					<a
						href={item.href}
						class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-colors font-medium {isActive(item.href)
							? 'bg-iris-2 text-iris-11 dark:bg-iris-3'
							: 'text-slate-11 hover:bg-slate-2 hover:text-slate-12 dark:hover:bg-slate-3'}"
						style={isActive(item.href) ? 
							'background-color: rgb(var(--iris-2)); color: rgb(var(--iris-11));' : 
							'color: rgb(var(--slate-11));'}
					>
						<svelte:component this={item.icon} class="h-5 w-5 flex-shrink-0" />
						<span>{item.label}</span>
					</a>
				{/each}
			</div>
		</nav>
		
		<!-- User Info & Logout -->
		<div class="p-4 border-t" style="border-color: rgb(var(--slate-6));">
			{#if $authStore.user}
				<div class="mb-3 px-2">
					<p class="text-sm font-medium truncate" style="color: rgb(var(--slate-12));">
						{$authStore.user.name}
					</p>
					<p class="text-xs truncate" style="color: rgb(var(--slate-11));">
						{$authStore.user.email}
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
	</aside>
	
	<!-- Main Content -->
	<main class="flex-1 overflow-y-auto" style="background-color: rgb(var(--background-color));">
		{@render children()}
	</main>
</div>

<style>
	/* Ensure proper color application */
	:global(.bg-iris-2) {
		background-color: rgb(var(--iris-2)) !important;
	}
	
	:global(.text-iris-11) {
		color: rgb(var(--iris-11)) !important;
	}
	
	:global(.dark .bg-iris-3) {
		background-color: rgb(var(--iris-3)) !important;
	}
	
	:global(.hover\:bg-slate-2:hover) {
		background-color: rgb(var(--slate-2));
	}
	
	:global(.hover\:text-slate-12:hover) {
		color: rgb(var(--slate-12));
	}
	
	:global(.dark .hover\:bg-slate-3:hover) {
		background-color: rgb(var(--slate-3));
	}
</style>
