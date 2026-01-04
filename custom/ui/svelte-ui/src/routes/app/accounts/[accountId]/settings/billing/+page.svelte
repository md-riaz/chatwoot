<script lang="ts">
  /**
   * Billing Settings Page
   * View and manage billing information
   */

  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';

  let currentPlan = $state({
    name: 'Pro',
    price: 49,
    billingCycle: 'monthly',
    features: [
      'Unlimited conversations',
      '10 team members',
      'Advanced analytics',
      'Custom branding',
      'Priority support',
    ],
  });

  let invoices = $state([
    {
      id: 'INV-001',
      date: '2024-01-01',
      amount: 49,
      status: 'paid',
    },
    {
      id: 'INV-002',
      date: '2023-12-01',
      amount: 49,
      status: 'paid',
    },
  ]);

  function handleUpgrade() {
    alert('Upgrade functionality coming soon!');
  }

  function handleDownload(invoiceId: string) {
    alert(`Downloading invoice ${invoiceId}`);
  }

  function getStatusBadge(status: string) {
    if (status === 'paid') return 'bg-green-100 text-green-800';
    if (status === 'pending') return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString();
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Billing</h1>
    <p class="text-muted-foreground mt-2">
      Manage your subscription and billing information
    </p>
  </div>

  <Card.Root>
    <Card.Header>
      <Card.Title>Current Plan</Card.Title>
      <Card.Description>Your active subscription plan</Card.Description>
    </Card.Header>
    <Card.Content>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-2xl font-bold">{currentPlan.name} Plan</h3>
          <p class="text-gray-600 mt-1">
            ${currentPlan.price} / {currentPlan.billingCycle}
          </p>
        </div>
        <Badge variant="default" class="text-sm px-4 py-2">Active</Badge>
      </div>

      <div class="space-y-2 mb-6">
        <h4 class="font-semibold">Included Features:</h4>
        <ul class="space-y-2">
          {#each currentPlan.features as feature}
            <li class="flex items-center gap-2">
              <svg
                class="h-5 w-5 text-green-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <span>{feature}</span>
            </li>
          {/each}
        </ul>
      </div>

      <div class="flex gap-2">
        <Button onclick={handleUpgrade}>Upgrade Plan</Button>
        <Button variant="outline">Manage Subscription</Button>
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root>
    <Card.Header>
      <Card.Title>Payment Method</Card.Title>
      <Card.Description>Your payment information</Card.Description>
    </Card.Header>
    <Card.Content>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="h-12 w-16 bg-gray-100 rounded flex items-center justify-center">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
              <rect
                x="2"
                y="5"
                width="20"
                height="14"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
              <line
                x1="2"
                y1="10"
                x2="22"
                y2="10"
                stroke="currentColor"
                stroke-width="2"
              />
            </svg>
          </div>
          <div>
            <p class="font-semibold">•••• •••• •••• 4242</p>
            <p class="text-sm text-gray-600">Expires 12/2025</p>
          </div>
        </div>
        <Button variant="outline">Update</Button>
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root>
    <Card.Header>
      <Card.Title>Invoice History</Card.Title>
      <Card.Description>View and download your past invoices</Card.Description>
    </Card.Header>
    <Card.Content>
      <div class="space-y-3">
        {#each invoices as invoice}
          <div class="flex items-center justify-between border-b pb-3">
            <div class="flex items-center gap-4">
              <div>
                <p class="font-semibold">{invoice.id}</p>
                <p class="text-sm text-gray-600">{formatDate(invoice.date)}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span class="font-semibold">${invoice.amount}.00</span>
              <span
                class="px-2 py-1 rounded text-xs font-medium {getStatusBadge(
                  invoice.status
                )}"
              >
                {invoice.status}
              </span>
              <Button
                variant="outline"
                size="sm"
                onclick={() => handleDownload(invoice.id)}
              >
                Download
              </Button>
            </div>
          </div>
        {/each}
      </div>
    </Card.Content>
  </Card.Root>
</div>
