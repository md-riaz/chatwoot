import { authStore } from '$lib/stores/auth.svelte';
import { globalConfig } from '$lib/stores/globalConfig.svelte';
import { redirect } from '@sveltejs/kit';

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
    featureFlag: 'agent_management',
  },
  {
    prefix: '/settings/assignment-policy',
    requiresAdmin: true,
    featureFlag: 'assignment_v2',
  },
  {
    prefix: '/settings/custom-roles',
    requiresAdmin: true,
    featureFlag: 'custom_roles',
  },
  { prefix: '/settings/security', requiresAdmin: true, featureFlag: 'saml' },
  {
    prefix: '/settings/attributes',
    requiresAdmin: true,
    featureFlag: 'custom_attributes',
  },
  {
    prefix: '/settings/automation',
    requiresAdmin: true,
    featureFlag: 'automations',
  },
  {
    prefix: '/settings/audit-logs',
    requiresAdmin: true,
    featureFlag: 'audit_logs',
  },
  {
    prefix: '/settings/inboxes',
    requiresAdmin: true,
    featureFlag: 'inbox_management',
  },
  { prefix: '/settings/macros', featureFlag: 'macros' },
  { prefix: '/settings/sla', requiresAdmin: true, featureFlag: 'sla' },
  { prefix: '/settings/billing', requiresAdmin: true },
  { prefix: '/settings/profile' },
  { prefix: '/settings/notifications' },
];

export function load({
  params,
  url,
}: {
  params: { accountId: string };
  url: URL;
}) {
  const targetAccountId = Number(params.accountId);
  const account = authStore.currentUser.accounts.find(
    acc => acc.id === targetAccountId
  );

  if (!account) {
    throw redirect(302, '/app/unauthorized');
  }

  const activeGuard = settingsRouteGuards.find(route =>
    url.pathname.includes(`/accounts/${targetAccountId}${route.prefix}`)
  );

  if (!activeGuard) {
    return { accountId: targetAccountId };
  }

  if (activeGuard.requiresAdmin && account.role !== 'administrator') {
    throw redirect(302, `/app/accounts/${targetAccountId}/settings/profile`);
  }

  if (activeGuard.featureFlag) {
    const accountFlagValue = account.features?.[activeGuard.featureFlag];
    const isEnabled =
      accountFlagValue !== undefined
        ? !!accountFlagValue
        : globalConfig.isFeatureEnabled(activeGuard.featureFlag);

    if (!isEnabled) {
      throw redirect(302, `/app/accounts/${targetAccountId}/settings`);
    }
  }

  return { accountId: targetAccountId };
}
