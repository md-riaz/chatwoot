
> @chatwoot/svelte-ui@1.0.0 check /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
> svelte-kit sync && svelte-check --tsconfig ./tsconfig.json

Loading svelte-check in workspace: /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
Getting Svelte diagnostics...

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/contacts.svelte.ts:180:9
Error: Type '(variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact | { updatedAt: string; accountId: number; contactId: number; ... 20 more ...; conversations?: any[] | undefined; }' is not assignable to type '((variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact) & ((variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact)'.
  Type '(variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact | { updatedAt: string; accountId: number; contactId: number; ... 20 more ...; conversations?: any[] | undefined; }' is not assignable to type '(variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact'.
    Type 'Contact | { updatedAt: string; accountId: number; contactId: number; avatar?: File | undefined; name: string; email: string | null; phoneNumber: string | null; identifier: string | null; ... 15 more ...; conversations?: any[] | undefined; }' is not assignable to type 'Contact'.
      Type '{ updatedAt: string; accountId: number; contactId: number; avatar?: File; name: string; email: string | null; phoneNumber: string | null; identifier: string | null; blocked: boolean; ... 14 more ...; conversations?: any[]; }' is not assignable to type 'Contact'.
        Types of property 'updatedAt' are incompatible.
          Type 'string' is not assignable to type 'number'. 
        // Optimistic update: immediately show changes
        optimisticUpdate: (variables) => {
          if (!this.originalContact) return this.originalContact as Contact;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/contacts.svelte.ts:181:45
Error: Conversion of type 'undefined' to type 'Contact' may be a mistake because neither type sufficiently overlaps with the other. If this was intentional, convert the expression to 'unknown' first. 
        optimisticUpdate: (variables) => {
          if (!this.originalContact) return this.originalContact as Contact;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/date-picker/date-picker.svelte:92:9
Error: Type 'DateValue | undefined' is not assignable to type 'DateValue[] | undefined'.
  Type 'CalendarDate' is missing the following properties from type 'DateValue[]': length, pop, push, concat, and 35 more. (ts)
      <Calendar
        value={value}
        onValueChange={handleCalendarChange}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/date-picker/date-picker.svelte:93:9
Error: Type '(v: DateValue | undefined) => void' is not assignable to type 'OnChangeFn<DateValue[]>'.
  Types of parameters 'v' and 'value' are incompatible.
    Type 'DateValue[]' is not assignable to type 'DateValue | undefined'.
      Type 'DateValue[]' is missing the following properties from type 'ZonedDateTime': #private, calendar, era, year, and 16 more. (ts)
        value={value}
        onValueChange={handleCalendarChange}
        {minValue}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:35:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('renders loading skeleton when isLoading is true', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:48:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('renders empty state when no data is provided', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:59:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('renders heatmap grid with correct structure', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:78:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('applies correct color scheme classes', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:97:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('handles green color scheme', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:113:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('has proper accessibility attributes', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:130:34
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('handles mouse interactions', async () => {
    const { component } = render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:150:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
    const numberOfRows = 3;
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/__tests__/BaseHeatmap.test.ts:164:12
Error: Argument of type 'Component<Props, {}, "">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<Props, {}, "">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
  it('handles error state correctly', () => {
    render(BaseHeatmap, {
      props: {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/carousel/carousel.svelte:24:3
Warn: This reference only captures the initial value of `orientation`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)
		scrollNext,
		orientation,
		canScrollNext: false,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/carousel/carousel.svelte:28:12
Warn: This reference only captures the initial value of `opts`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)
		handleKeyDown,
		options: opts,
		plugins,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/carousel/carousel.svelte:29:3
Warn: This reference only captures the initial value of `plugins`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)
		options: opts,
		plugins,
		onInit,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.svelte:8:34
Error: File '/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/node_modules/.pnpm/@kevwpl+svelte-o-phone@0.0.10_svelte@5.46.4/node_modules/@kevwpl/svelte-o-phone/src/lib/index.ts' is not a module. (ts)
  import { cn } from '$lib/utils';
  import { usePhonePicker } from '@kevwpl/svelte-o-phone';
  import { tick } from 'svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/contact-form/contact-form.svelte:20:44
Warn: This reference only captures the initial value of `contact`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)

  let form = $state(extractContactFormData(contact));
  let phoneCountry = $state('US');

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/custom-attributes/DateAttributeInput.svelte:92:12
Error: Type 'DateValue | undefined' is not assignable to type 'DateValue[] | undefined'.
  Type 'CalendarDate' is missing the following properties from type 'DateValue[]': length, pop, push, concat, and 35 more. (ts)
    <Calendar
      bind:value={dateValue}
      {disabled}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.test.ts:5:23
Error: Cannot find module '@testing-library/user-event' or its corresponding type declarations. 
import PhoneInput from './phone-input.svelte';
import userEvent from '@testing-library/user-event';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.test.ts:12:16
Error: Argument of type 'Component<$$ComponentProps, {}, "value" | "country">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<$$ComponentProps, {}, "value" | "country">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
    it('renders correctly with default props', () => {
        render(PhoneInput, {
            country: 'US',

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.test.ts:25:16
Error: Argument of type 'Component<$$ComponentProps, {}, "value" | "country">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<$$ComponentProps, {}, "value" | "country">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
        const user = userEvent.setup();
        render(PhoneInput);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.test.ts:36:38
Error: Argument of type 'Component<$$ComponentProps, {}, "value" | "country">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<$$ComponentProps, {}, "value" | "country">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
        const user = userEvent.setup();
        const { component } = render(PhoneInput, { country: 'US' });

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.test.ts:62:16
Error: Argument of type 'Component<$$ComponentProps, {}, "value" | "country">' is not assignable to parameter of type 'Constructor<SvelteComponent<Record<string, any>, any, any>>'.
  Type 'Component<$$ComponentProps, {}, "value" | "country">' provides no match for the signature 'new (...args: any[]): SvelteComponent<Record<string, any>, any, any>'. 
        const user = userEvent.setup();
        render(PhoneInput, { country: 'US' });

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/toggle-group/toggle-group.svelte:27:30
Warn: This reference only captures the initial value of `variant`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)
	// Create reactive context
	let contextValue = $state({ variant, size });

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/toggle-group/toggle-group.svelte:27:39
Warn: This reference only captures the initial value of `size`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)
	// Create reactive context
	let contextValue = $state({ variant, size });

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/toggle-group/toggle-group.svelte:33:20
Warn: This reference only captures the initial value of `contextValue`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
	
	setToggleGroupCtx(contextValue);
</script>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/event-manager.ts:184:16
Error: Property 'updatePresence' does not exist on type 'ReverbClient'. 
        // Update presence on server
        client.updatePresence?.();
      } catch (error) {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:106:22
Error: Tuple type '[]' of length '0' has no element at index '1'. 
      const messageCreatedCall = mockReverbClient.subscribePrivate.mock.calls.find(
        call => call[1] === 'message.created'
      );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:108:51
Error: Tuple type '[]' of length '0' has no element at index '2'. 
      );
      const messageHandler = messageCreatedCall?.[2];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:134:7
Error: This expression is not callable.
  Type 'never' has no call signatures. 

      messageHandler?.(messageData);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:152:22
Error: Tuple type '[]' of length '0' has no element at index '0'. 
      const presenceCall = mockReverbClient.subscribePresence.mock.calls.find(
        call => call[0] === `account.${accountId}.presence`
      );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:154:48
Error: Tuple type '[]' of length '0' has no element at index '1'. 
      );
      const presenceCallbacks = presenceCall?.[1];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:166:26
Error: Property 'onMemberAdded' does not exist on type 'never'. 

      presenceCallbacks?.onMemberAdded?.(userData);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:181:22
Error: Tuple type '[]' of length '0' has no element at index '1'. 
      const typingOnCall = mockReverbClient.subscribePrivate.mock.calls.find(
        call => call[1] === 'conversation.typing_on'
      );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:183:44
Error: Tuple type '[]' of length '0' has no element at index '2'. 
      );
      const typingHandler = typingOnCall?.[2];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:203:7
Error: This expression is not callable.
  Type 'never' has no call signatures. 

      typingHandler?.(typingData);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:220:22
Error: Tuple type '[]' of length '0' has no element at index '1'. 
      const notificationCall = mockReverbClient.subscribePrivate.mock.calls.find(
        call => call[1] === 'notification.created'
      );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:222:54
Error: Tuple type '[]' of length '0' has no element at index '2'. 
      );
      const notificationHandler = notificationCall?.[2];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:242:7
Error: This expression is not callable.
  Type 'never' has no call signatures. 

      notificationHandler?.(notificationData);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/integration-test.ts:345:21
Error: Cannot find name 'wsStore'. 
      presence: presenceStore.users,
      wsConnection: wsStore.stats
    })

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:393:13
Warn: A form label must be associated with a control
https://svelte.dev/e/a11y_label_has_associated_control (svelte)
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:406:13
Warn: A form label must be associated with a control
https://svelte.dev/e/a11y_label_has_associated_control (svelte)
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:418:13
Warn: A form label must be associated with a control
https://svelte.dev/e/a11y_label_has_associated_control (svelte)
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
            <input

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:427:13
Warn: A form label must be associated with a control
https://svelte.dev/e/a11y_label_has_associated_control (svelte)
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
            <input

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:34:5
Error: Object literal may only specify known properties, and 'company' does not exist in type 'CreateContactParams'. (ts)
    phoneNumber: '',
    company: ''
  });

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:41:54
Error: Property 'total' does not exist on type 'PaginatedResponse<Contact>'. (ts)
  const hasError = $derived(contacts.hasAnyError);
  const totalContacts = $derived(contacts.list.data?.total || 0);
  const currentPage = $derived(contacts.list.data?.currentPage || 1);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:42:52
Error: Property 'currentPage' does not exist on type 'PaginatedResponse<Contact>'. (ts)
  const totalContacts = $derived(contacts.list.data?.total || 0);
  const currentPage = $derived(contacts.list.data?.currentPage || 1);
  const totalPages = $derived(contacts.list.data?.lastPage || 1);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:43:51
Error: Property 'lastPage' does not exist on type 'PaginatedResponse<Contact>'. (ts)
  const currentPage = $derived(contacts.list.data?.currentPage || 1);
  const totalPages = $derived(contacts.list.data?.lastPage || 1);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:79:60
Error: Object literal may only specify known properties, and 'company' does not exist in type 'CreateContactParams'. (ts)
      showCreateModal = false;
      createForm = { name: '', email: '', phoneNumber: '', company: '' };

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:91:46
Error: Argument of type 'Partial<Contact>' is not assignable to parameter of type 'UpdateContactParams'.
  Types of property 'email' are incompatible.
    Type 'string | null | undefined' is not assignable to type 'string | undefined'.
      Type 'null' is not assignable to type 'string | undefined'. (ts)
  async function handleUpdateContact(contact: Contact, updates: Partial<Contact>) {
    await contacts.updateContact(contact.id, updates, contact);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:429:38
Error: Property 'company' does not exist on type 'CreateContactParams'. (ts)
            <input
              bind:value={createForm.company}
              type="text"

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatFilters.svelte:5:31
Error: Cannot find module '$lib/components/ui/date-range-picker/DateRangePicker.svelte' or its corresponding type declarations. (ts)
  import { Label } from '$lib/components/ui/label';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatFilters.svelte:25:39
Error: Property 'getAgents' does not exist on type 'AgentsStore'. (ts)

  const agents = $derived(agentsStore.getAgents());
  const inboxes = $derived(inboxesStore.getInboxes());

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatFilters.svelte:26:41
Error: Property 'getInboxes' does not exist on type 'InboxesStore'. Did you mean 'fetchInboxes'? (ts)
  const agents = $derived(agentsStore.getAgents());
  const inboxes = $derived(inboxesStore.getInboxes());
  const teams = $derived(teamsStore.getTeams());

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatFilters.svelte:27:37
Error: Property 'getTeams' does not exist on type 'TeamsStore'. (ts)
  const inboxes = $derived(inboxesStore.getInboxes());
  const teams = $derived(teamsStore.getTeams());

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatMetrics.svelte:12:38
Error: Property 'getMetrics' does not exist on type 'CSATStore'. Did you mean 'metrics'? (ts)

  const metrics = $derived(csatStore.getMetrics());
  const isLoading = $derived(csatStore.getUIFlags().isFetchingMetrics);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:214:6
Error: Object literal may only specify known properties, and 'title' does not exist in type 'Props'. (ts)
  <MetricCard 
    {title}
    isLive={true}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportChart.svelte:5:24
Error: Cannot find module 'svelte-chartjs' or its corresponding type declarations. (ts)
  import LoadingSkeleton from './LoadingSkeleton.svelte';
  import { Line } from 'svelte-chartjs';
  import {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportChart.svelte:15:10
Error: Cannot find module 'chart.js' or its corresponding type declarations. (ts)
    PointElement,
  } from 'chart.js';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportChart.svelte:41:43
Error: Property 'getChartData' does not exist on type 'ReportsStore'. (ts)

  const chartData = $derived(reportsStore.getChartData(metricKey));
  const isLoading = $derived(reportsStore.getUIFlag(`isFetching_${metricKey}`));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportChart.svelte:42:43
Error: Property 'getUIFlag' does not exist on type 'ReportsStore'. (ts)
  const chartData = $derived(reportsStore.getChartData(metricKey));
  const isLoading = $derived(reportsStore.getUIFlag(`isFetching_${metricKey}`));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportContainer.svelte:21:48
Error: Property 'getData' does not exist on type 'ReportsStore'. (ts)

  const accountSummary = $derived(reportsStore.getData(accountSummaryKey));
  const isFetchingSummary = $derived(reportsStore.getUIFlag(summaryFetchingKey));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportContainer.svelte:22:51
Error: Property 'getUIFlag' does not exist on type 'ReportsStore'. (ts)
  const accountSummary = $derived(reportsStore.getData(accountSummaryKey));
  const isFetchingSummary = $derived(reportsStore.getUIFlag(summaryFetchingKey));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:6:31
Error: Cannot find module '$lib/components/ui/date-range-picker/DateRangePicker.svelte' or its corresponding type declarations. (ts)
  import { Switch } from '$lib/components/ui/switch';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:29:42
Error: Property 'label' does not exist on type 'GroupByFilter'. (ts)

  const groupByLabel = $derived(groupBy?.label || 'Select grouping');

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:85:72
Error: Property 'label' does not exist on type 'GroupByFilter'. (ts)
            {#each Object.values(GROUP_BY_FILTER) as filter}
              <Select.Item value={filter.id?.toString()} label={filter.label}>
                {filter.label}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:86:25
Error: Property 'label' does not exist on type 'GroupByFilter'. (ts)
              <Select.Item value={filter.id?.toString()} label={filter.label}>
                {filter.label}
              </Select.Item>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilters.svelte:30:36
Warn: This reference only captures the initial value of `currentFilterId`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)

  let selectedFilterValue = $state(currentFilterId);
  let selectedGroupByValue = $state(selectedGroupById);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilters.svelte:31:37
Warn: This reference only captures the initial value of `selectedGroupById`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
  let selectedFilterValue = $state(currentFilterId);
  let selectedGroupByValue = $state(selectedGroupById);
  let businessHoursEnabled = $state(false);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilters.svelte:6:31
Error: Cannot find module '$lib/components/ui/date-range-picker/DateRangePicker.svelte' or its corresponding type declarations. (ts)
  import { Switch } from '$lib/components/ui/switch';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:59:31
Warn: This reference only captures the initial value of `selectedItemRef`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
  const selectedItemRef = $derived(selectedItem);
  let selectedFilter = $state(selectedItemRef);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:68:30
Error: Property 'getFilterItems' does not exist on type 'ReportsStore'. (ts)
  const filterItemsList = $derived(
    getterKey ? reportsStore.getFilterItems(getterKey) : []
  );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:87:20
Error: Property 'dispatchAction' does not exist on type 'ReportsStore'. (ts)
    if (actionKey) {
      reportsStore.dispatchAction(actionKey);
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:93:20
Error: Property 'fetchAccountSummary' does not exist on type 'ReportsStore'. (ts)
    if (selectedFilter) {
      reportsStore.fetchAccountSummary({
        from,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:108:28
Error: Property 'fetchAccountReport' does not exist on type 'ReportsStore'. Did you mean 'fetchAccountReports'? (ts)
      try {
        await reportsStore.fetchAccountReport({
          metric: reportKeys[key as keyof typeof reportKeys],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:134:20
Error: Property 'dispatchAction' does not exist on type 'ReportsStore'. (ts)
      const params = { from, to, fileName, businessHours };
      reportsStore.dispatchAction(dispatchMethods[type], params);
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:69:5
Warn: Unknown at rule @apply (css)
  .presence-indicator {
    @apply inline-flex items-center;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:73:5
Warn: Unknown at rule @apply (css)
  .presence-indicator__dot {
    @apply flex-shrink-0;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:38:5
Warn: Unknown at rule @apply (css)
  .typing-indicator {
    @apply flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:42:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__content {
    @apply flex items-center gap-2;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:46:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__dots {
    @apply flex items-center gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:50:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__dot {
    @apply w-1.5 h-1.5 bg-muted-foreground rounded-full;
    animation: typing-pulse 1.4s infinite ease-in-out;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:63:5
Warn: Unknown at rule @apply (css)
  .typing-indicator__text {
    @apply text-xs;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:68:7
Warn: Unknown at rule @apply (css)
    0%, 80%, 100% {
      @apply opacity-30 scale-75;
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/typing-indicator.svelte:71:7
Warn: Unknown at rule @apply (css)
    40% {
      @apply opacity-100 scale-100;
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:86:5
Warn: Unknown at rule @apply (css)
  .websocket-status {
    @apply flex flex-col gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:90:5
Warn: Unknown at rule @apply (css)
  .websocket-status__details {
    @apply flex flex-col gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:94:5
Warn: Unknown at rule @apply (css)
  .websocket-status__error {
    @apply flex items-center gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:99:5
Warn: Unknown at rule @apply (css)
  .websocket-status__subscriptions {
    @apply flex items-center;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:48:11
Error: Type 'string' is not assignable to type '"default" | "destructive" | "outline" | "secondary" | undefined'. (ts)
<div class="websocket-status {className}">
  <Badge {variant} class="flex items-center gap-1.5">
    {@const Icon = icon}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/advanced-filter.svelte:180:16
Error: Type 'string | number | boolean' is not assignable to type 'string'.
  Type 'number' is not assignable to type 'string'. (ts)
      ...newFilters[index],
      values: [option.id],
    };

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/advanced-filter.svelte:376:32
Error: This comparison appears to be unintentional because the types 'string[]' and 'string | number | boolean' have no overlap. (ts)
                          {option.name}
                          {#if filter.values === option.id}
                            <Check class="h-4 w-4 text-blue-600" />

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/bulk-action-bar.svelte:94:13
Error: Type 'boolean | "indeterminate"' is not assignable to type 'boolean | undefined'.
  Type '"indeterminate"' is not assignable to type 'boolean | undefined'. (ts)
          <Checkbox
            checked={allSelected
              ? true

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/select/select.svelte:15:38
Error: Type 'string | string[] | undefined' is not assignable to type 'string[] | undefined'.
  Type 'string' is not assignable to type 'string[]'. (ts)

<SelectPrimitive.Root bind:open bind:value { ...restProps } />

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:94:5
Warn: Unknown at rule @apply (css)
  .chat-bubble {
    @apply max-w-[85%] cursor-pointer p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:98:5
Warn: Unknown at rule @apply (css)
  .row--agent-block {
    @apply items-center flex text-left pb-2 text-xs;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:102:5
Warn: Unknown at rule @apply (css)
  .agent--name {
    @apply font-medium ml-1 text-gray-900;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:106:5
Warn: Unknown at rule @apply (css)
  .company--name {
    @apply text-gray-600 ml-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:110:5
Warn: Unknown at rule @apply (css)
  .message-content {
    @apply text-sm text-gray-800 leading-relaxed;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:114:5
Warn: Unknown at rule @apply (css)
  .message-content :global(br) {
    @apply block mb-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:125:5
Warn: Unknown at rule @apply (css)
  .unread-messages {
    @apply pb-2;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:130:5
Warn: Unknown at rule @apply (css)
    transition: all 0.3s cubic-bezier(0.17, 0.67, 0.83, 0.67);
    @apply bg-transparent border-none border-0 font-semibold text-base ml-1 py-0 pl-0 pr-2.5 hover:brightness-75 hover:translate-x-1;
    color: var(--widget-color, #1f93ff);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:136:5
Warn: Unknown at rule @apply (css)
    transition: all 0.3s cubic-bezier(0.17, 0.67, 0.83, 0.67);
    @apply bg-gray-100 text-gray-800 hover:bg-gray-200 border-none border-0 font-medium text-xs rounded-2xl mb-3 px-3 py-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignView.svelte:140:5
Warn: Unknown at rule @apply (css)
  .button {
    @apply cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:265:5
Warn: Unknown at rule @apply (css)
  .widget-app {
    @apply w-full h-full;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:271:5
Warn: Unknown at rule @apply (css)
  .home-view {
    @apply p-4;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:275:5
Warn: Unknown at rule @apply (css)
  .campaign-notification {
    @apply mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:279:5
Warn: Unknown at rule @apply (css)
  .campaign-notification button {
    @apply mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:96:31
Error: Property 'handleExecuteCampaign' is private and only accessible within class 'WidgetCampaignManager'. (ts)
      try {
        await campaignManager.handleExecuteCampaign(event);
        currentRoute = 'messages';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:227:8
Error: Object literal may only specify known properties, and 'messageCount' does not exist in type 'Props'. (ts)
      {widgetColor}
      {messageCount}
      useInboxAvatarForBot={true}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/WidgetApp.svelte:254:54
Error: Argument of type '{ detail: { campaignId: number; }; }' is not assignable to parameter of type 'CustomEvent<{ campaignId: number; }>'.
  Type '{ detail: { campaignId: number; }; }' is missing the following properties from type 'CustomEvent<{ campaignId: number; }>': initCustomEvent, bubbles, cancelBubble, cancelable, and 19 more. (ts)
          <p>Campaign ready: {activeCampaign.title}</p>
          <button onclick={() => handleCampaignClick({ detail: { campaignId: activeCampaign.id } })}>
            View Campaign

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:447:43
Error: Argument of type 'number' is not assignable to parameter of type 'string'. (ts)
                <Calendar class="h-4 w-4" />
                <span>Created {formatDate(contact.createdAt)}</span>
              </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:453:21
Error: Argument of type 'number | null' is not assignable to parameter of type 'string | null | undefined'.
  Type 'number' is not assignable to type 'string'. (ts)
                  >Last activity {formatRelativeTime(
                    contact.lastActivityAt
                  )}</span

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:566:80
Error: Type 'unknown' is not assignable to type 'string | null | undefined'. (ts)
                                    <span class="text-sm text-muted-foreground">{formatSocialLabel(sk)}:</span>
                                    <a class="text-sm text-blue-600 underline" href={String(sv).startsWith('http') ? sv : `https://${String(sv)}`} target="_blank" rel="noopener noreferrer">{sv}</a>
                                  </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:665:82
Error: Type 'unknown' is not assignable to type 'string | null | undefined'. (ts)
                                      <span class="text-sm text-muted-foreground">{formatSocialLabel(sk)}:</span>
                                      <a class="text-sm text-blue-600 underline" href={String(sv).startsWith('http') ? sv : `https://${String(sv)}`} target="_blank" rel="noopener noreferrer">{sv}</a>
                                    </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/bot/+page.svelte:4:26
Error: Cannot find module '$lib/components/reports/bot/BotMetrics.svelte' or its corresponding type declarations. (ts)
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import BotMetrics from '$lib/components/reports/bot/BotMetrics.svelte';
  import ReportFilterSelector from '$lib/components/reports/shared/ReportFilterSelector.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/bot/+page.svelte:32:20
Error: Property 'fetchBotSummary' does not exist on type 'ReportsStore'. (ts)
    try {
      reportsStore.fetchBotSummary(getRequestPayload());
    } catch (error) {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/bot/+page.svelte:41:28
Error: Property 'fetchAccountReport' does not exist on type 'ReportsStore'. Did you mean 'fetchAccountReports'? (ts)
      try {
        await reportsStore.fetchAccountReport({
          metric: reportKeys[key as keyof typeof reportKeys],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/conversation/+page.svelte:35:20
Error: Property 'fetchAccountSummary' does not exist on type 'ReportsStore'. (ts)
    try {
      reportsStore.fetchAccountSummary(getRequestPayload());
    } catch (error) {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/conversation/+page.svelte:44:28
Error: Property 'fetchAccountReport' does not exist on type 'ReportsStore'. Did you mean 'fetchAccountReports'? (ts)
      try {
        await reportsStore.fetchAccountReport({
          metric: REPORTS_KEYS[key as keyof typeof REPORTS_KEYS],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/conversation/+page.svelte:69:18
Error: Property 'downloadConversationsSummaryReports' does not exist on type 'ReportsStore'. (ts)
    });
    reportsStore.downloadConversationsSummaryReports({
      from,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/csat/+page.svelte:38:17
Error: Property 'getMetrics' does not exist on type 'CSATStore'. Did you mean 'metrics'? (ts)
    try {
      csatStore.getMetrics(requestPayload);
      getResponses();

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:4:26
Error: Cannot find module '$lib/components/reports/sla/SLAMetrics.svelte' or its corresponding type declarations. (ts)
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import SLAMetrics from '$lib/components/reports/sla/SLAMetrics.svelte';
  import SLATable from '$lib/components/reports/sla/SLATable.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:5:24
Error: Cannot find module '$lib/components/reports/sla/SLATable.svelte' or its corresponding type declarations. (ts)
  import SLAMetrics from '$lib/components/reports/sla/SLAMetrics.svelte';
  import SLATable from '$lib/components/reports/sla/SLATable.svelte';
  import SLAReportFilters from '$lib/components/reports/sla/SLAReportFilters.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:6:32
Error: Cannot find module '$lib/components/reports/sla/SLAReportFilters.svelte' or its corresponding type declarations. (ts)
  import SLATable from '$lib/components/reports/sla/SLATable.svelte';
  import SLAReportFilters from '$lib/components/reports/sla/SLAReportFilters.svelte';
  import { Button } from '$lib/components/ui/button';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:29:47
Error: Property 'getMetrics' does not exist on type 'SLAReportsStore'. Did you mean 'metrics'? (ts)
  const slaReports = $derived(slaReportsStore.getAll());
  const slaMetrics = $derived(slaReportsStore.getMetrics());
  const slaMeta = $derived(slaReportsStore.getMeta());

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:38:14
Error: Property 'fetchSLAs' does not exist on type 'SLAStore'. (ts)
    labelsStore.fetchLabels();
    slaStore.fetchSLAs();
    fetchSLAMetrics();

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:44:25
Error: Argument of type '{ from: number; to: number; assigned_agent_id: null; inbox_id: null; team_id: null; sla_policy_id: null; label_list: null; page: number; }' is not assignable to parameter of type 'SLAFilterParams & { accountId: number; }'.
  Property 'accountId' is missing in type '{ from: number; to: number; assigned_agent_id: null; inbox_id: null; team_id: null; sla_policy_id: null; label_list: null; page: number; }' but required in type '{ accountId: number; }'. (ts)
  function fetchSLAReports({ pageNumber: page }: { pageNumber?: number } = {}) {
    slaReportsStore.get({
      page: page || pageNumber,
      ...activeFilter,
    });
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:51:21
Error: Property 'getMetrics' does not exist on type 'SLAReportsStore'. Did you mean 'metrics'? (ts)
  function fetchSLAMetrics() {
    slaReportsStore.getMetrics(activeFilter);
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:67:32
Error: Argument of type '{ from: number; to: number; assigned_agent_id: null; inbox_id: null; team_id: null; sla_policy_id: null; label_list: null; fileName: string; }' is not assignable to parameter of type 'SLAFilterParams & { accountId: number; fileName: string; }'.
  Property 'accountId' is missing in type '{ from: number; to: number; assigned_agent_id: null; inbox_id: null; team_id: null; sla_policy_id: null; label_list: null; fileName: string; }' but required in type '{ accountId: number; fileName: string; }'. (ts)
    try {
      slaReportsStore.download({
        fileName: generateFileName({ type, to: activeFilter.to }),
        ...activeFilter,
      });
    } catch (error) {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:97:29
Error: 'slaMeta' is possibly 'null'. (ts)
        isLoading={uiFlags.isFetching}
        currentPage={Number(slaMeta.currentPage)}
        totalCount={Number(slaMeta.count)}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:98:28
Error: 'slaMeta' is possibly 'null'. (ts)
        currentPage={Number(slaMeta.currentPage)}
        totalCount={Number(slaMeta.count)}
        on:page-change={onPageChange}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountId.svelte:18:2
Error: Property 'headerActions' is missing in type '{ children: () => any; title: string; description: string; withBorder: true; }' but required in type '$$ComponentProps'. (ts)

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.ACCOUNT_ID.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/BuildInfo.svelte:8:66
Error: Property 'latest_chatwoot_version' does not exist on type 'UserAccount'. Did you mean 'latestChatwootVersion'? (ts)

  let latestChatwootVersion = $derived(authStore.currentAccount?.latest_chatwoot_version);
  let appVersion = $derived(globalConfig.get('appVersion'));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:10:56
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. Did you mean 'customAttributes'? (ts)
  let currentAccount = $derived(authStore.currentAccount);
  let isMarkedForDeletion = $derived(!!currentAccount?.custom_attributes?.marked_for_deletion_at);
  let markedForDeletionDate = $derived(

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:12:21
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. Did you mean 'customAttributes'? (ts)
  let markedForDeletionDate = $derived(
    currentAccount?.custom_attributes?.marked_for_deletion_at 
      ? new Date(currentAccount.custom_attributes.marked_for_deletion_at)

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:13:33
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. Did you mean 'customAttributes'? (ts)
    currentAccount?.custom_attributes?.marked_for_deletion_at 
      ? new Date(currentAccount.custom_attributes.marked_for_deletion_at) 
      : null

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:17:21
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. Did you mean 'customAttributes'? (ts)
  let markedForDeletionReason = $derived(
    currentAccount?.custom_attributes?.marked_for_deletion_reason || 'manual_deletion'
  );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:59:2
Error: Property 'headerActions' is missing in type '{ children: () => any; title: string; description: string; withBorder: true; }' but required in type '$$ComponentProps'. (ts)

<SectionLayout
  title={$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AudioTranscription.svelte:34:2
Error: Property 'children' is missing in type '{ title: string; description: string; withBorder: true; headerActions: () => any; }' but required in type '$$ComponentProps'. (ts)

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.AUDIO_TRANSCRIPTION.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/+page.svelte:107:6
Error: Property 'headerActions' is missing in type '{ children: () => any; title: string; description: string; }' but required in type '$$ComponentProps'. (ts)
    <!-- General Settings Section -->
    <SectionLayout
      title={$_('GENERAL_SETTINGS.FORM.GENERAL_SECTION.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/ui/[name]/+page.svelte:129:30
Error: `$state(...)` can only be used as a variable declaration initializer, a class field declaration, or the first assignment to a class field at the top level of the constructor.
https://svelte.dev/e/state_invalid_placement (svelte)
      {#snippet selectDemo()}
        {@const themeValue = $state('light')}
        {@const themeLabel = $derived(

====================================
svelte-check found 102 errors and 41 warnings in 40 files
 ELIFECYCLE  Command failed with exit code 1.
