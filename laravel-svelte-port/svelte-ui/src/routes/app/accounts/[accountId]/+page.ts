
import { redirect } from '@sveltejs/kit';
import type { PageLoad } from './$types';

export const load: PageLoad = async ({ params }) => {
  const { accountId } = params;
  throw redirect(302, `/app/accounts/${accountId}/inbox-view`);
};
