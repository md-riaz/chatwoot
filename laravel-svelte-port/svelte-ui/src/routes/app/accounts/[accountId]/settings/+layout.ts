import { authStore } from '$lib/stores/auth.svelte';
import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';

export const ssr = false;

type SettingsRouteGuard = {
  prefix: string;
  requiresAdmin?: boolean;
  featureFlag?: string;
};

const settingsRouteGuards: SettingsRouteGuard[] = [
  { prefix: '/settings/account', requiresAdmin: true },
  {
    prefix: '/settings/agents',
    requiresAdmin: true,
    featureFlag: 'agentManagement',
  },
  { prefix: '/settings/agent-bots', requiresAdmin: true },
  {
    prefix: '/settings/assignment-policy',
    requiresAdmin: true,
    featureFlag: 'assignmentV2',
  },
  { prefix: '/settings/canned', requiresAdmin: true },
  {
    prefix: '/settings/custom-roles',
    requiresAdmin: true,
    featureFlag: 'customRoles',
  },
  { prefix: '/settings/integrations', requiresAdmin: true },
  { prefix: '/settings/labels', requiresAdmin: true },
  { prefix: '/settings/security', requiresAdmin: true, featureFlag: 'saml' },
  {
    prefix: '/settings/attributes',
    requiresAdmin: true,
    featureFlag: 'customAttributes',
  },
  {
    prefix: '/settings/automation',
    requiresAdmin: true,
    featureFlag: 'automations',
  },
  {
    prefix: '/settings/audit-logs',
    requiresAdmin: true,
    featureFlag: 'auditLogs',
  },
  {
    prefix: '/settings/inboxes',
    requiresAdmin: true,
    featureFlag: 'inboxManagement',
  },
  { prefix: '/settings/macros', featureFlag: 'macros' },
  { prefix: '/settings/sla', requiresAdmin: true, featureFlag: 'sla' },
  { prefix: '/settings/teams', requiresAdmin: true },
  { prefix: '/settings/billing', requiresAdmin: true },
  { prefix: '/settings/profile' },
  { prefix: '/settings/notifications' },
];

export const load: LayoutLoad = ({ params, url }) => {
  const targetAccountId = Number(params.accountId);

  console.log('DEBUG: Settings Guard - Params AccountId:', params.accountId);
  console.log('DEBUG: Settings Guard - Current User Accounts:', authStore.currentUser?.accounts?.length);

  if (!authStore.currentUser?.accounts || isNaN(targetAccountId)) {
    console.log('DEBUG: Settings Guard - No accounts or targetAccountId is NaN, redirecting to unauthorized');
    throw redirect(302, '/app/unauthorized');
  }

  if (!authStore.currentUser?.accounts || authStore.currentUser.accounts.length === 0) {
    console.log('DEBUG: Settings layout - No accounts found, redirecting to unauthorized');
    throw redirect(302, '/app/unauthorized');
  }

  const account = authStore.currentUser.accounts.find(
    acc => acc.id === targetAccountId
  );

  if (!account) {
    throw redirect(302, '/app/unauthorized');
  }

  if (url.pathname === `/app/accounts/${targetAccountId}/settings`) {
    return { accountId: targetAccountId };
  }

  const activeGuard = settingsRouteGuards.find(route =>
    url.pathname.startsWith(`/app/accounts/${targetAccountId}${route.prefix}`)
  );

  if (!activeGuard) {
    throw redirect(302, '/app/unauthorized');
  }

  if (activeGuard.requiresAdmin && !authStore.hasPermission('administrator', targetAccountId)) {
    throw redirect(302, `/app/accounts/${targetAccountId}/settings/profile`);
  }

  if (
    activeGuard.featureFlag &&
    !authStore.isFeatureEnabled(activeGuard.featureFlag, targetAccountId)
  ) {
    throw redirect(302, `/app/accounts/${targetAccountId}/settings`);
  }

  return { accountId: targetAccountId };
};
