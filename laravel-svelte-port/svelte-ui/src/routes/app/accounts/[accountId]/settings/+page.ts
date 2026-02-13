import { authStore } from '$lib/stores/auth.svelte';
import { globalConfig } from '$lib/stores/globalConfig.svelte';
import { redirect } from '@sveltejs/kit';
import type { PageLoad } from './$types';

export const load: PageLoad = ({ params }) => {
  const accountId = Number(params.accountId);

  if (!authStore.currentUser?.accounts) {
    throw redirect(302, '/app/unauthorized');
  }

  const account = authStore.currentUser.accounts.find(
    currentAccount => currentAccount.id === accountId
  );

  if (!account) {
    throw redirect(302, '/app/unauthorized');
  }

  if (account.role === 'administrator' && account.customRoleId == null) {
    throw redirect(302, `/app/accounts/${accountId}/settings/account`);
  }

  const macrosEnabled =
    account.features?.macros ?? globalConfig.isFeatureEnabled('macros');

  if (macrosEnabled) {
    throw redirect(302, `/app/accounts/${accountId}/settings/macros`);
  }

  throw redirect(302, `/app/accounts/${accountId}/settings/profile`);
};
