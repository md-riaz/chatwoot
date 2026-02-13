import { redirect } from '@sveltejs/kit';

export function load({ params }: { params: { accountId: string } }) {
  throw redirect(302, `/app/accounts/${params.accountId}/reports/label`);
}
