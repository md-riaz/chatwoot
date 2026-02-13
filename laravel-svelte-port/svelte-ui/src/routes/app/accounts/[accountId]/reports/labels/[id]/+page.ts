import { redirect } from '@sveltejs/kit';

export function load({
  params,
}: {
  params: { accountId: string; id: string };
}) {
  throw redirect(
    302,
    `/app/accounts/${params.accountId}/reports/label?labelId=${params.id}`
  );
}
