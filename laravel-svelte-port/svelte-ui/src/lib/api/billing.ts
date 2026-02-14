import { show } from './accounts';

export interface BillingPlan {
  name: string;
  amount: number;
  billingCycle: string;
  features: string[];
  status: string;
}

export interface BillingInvoice {
  id: string;
  date: string;
  amount: number;
  status: string;
  downloadUrl?: string;
}

export async function getCurrentPlan(accountId: number): Promise<BillingPlan> {
  const account = await show(accountId);

  return {
    name: 'Workspace Plan',
    amount: 0,
    billingCycle: 'monthly',
    features: Object.keys(account.features || {})
      .slice(0, 5)
      .map(key =>
        key.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase())
      ),
    status: account.status || 'active',
  };
}

export async function getInvoices(
  _accountId: number
): Promise<BillingInvoice[]> {
  return [];
}
