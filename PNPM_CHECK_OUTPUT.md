
> @chatwoot/svelte-ui@1.0.0 check
> svelte-kit sync && svelte-check --tsconfig ./tsconfig.json

Loading svelte-check in workspace: /mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui
Getting Svelte diagnostics...

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/base.svelte.ts:301:14
Error: Property 'options' is private and only accessible within class 'BaseAction<TData, TVariables, TError>'. 
    // Apply optimistic update if provided
    if (this.options.optimisticUpdate) {
      this.optimisticData = this.data;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/base.svelte.ts:301:22
Error: Property 'optimisticUpdate' does not exist on type 'ActionOptions<TData, TVariables>'. 
    // Apply optimistic update if provided
    if (this.options.optimisticUpdate) {
      this.optimisticData = this.data;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/base.svelte.ts:303:24
Error: Property 'options' is private and only accessible within class 'BaseAction<TData, TVariables, TError>'. 
      this.optimisticData = this.data;
      this.data = this.options.optimisticUpdate(variables);
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/base.svelte.ts:303:32
Error: Property 'optimisticUpdate' does not exist on type 'ActionOptions<TData, TVariables>'. 
      this.optimisticData = this.data;
      this.data = this.options.optimisticUpdate(variables);
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/base.svelte.ts:315:15
Error: Property 'options' is private and only accessible within class 'BaseAction<TData, TVariables, TError>'. 
        this.optimisticData = null;
        (this.options as any).onRollback?.(variables);
      }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:107:5
Error: Spread types may only be created from object types. 
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:108:11
Error: 'response' is of type 'unknown'. 
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  };

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:129:5
Error: Spread types may only be created from object types. 
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:130:11
Error: 'response' is of type 'unknown'. 
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  };

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:169:5
Error: Spread types may only be created from object types. 
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:170:11
Error: 'response' is of type 'unknown'. 
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  };

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:189:5
Error: Spread types may only be created from object types. 
  return {
    ...response,
    data: response.data?.map(transformContactFromApi) || [],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:190:11
Error: 'response' is of type 'unknown'. 
    ...response,
    data: response.data?.map(transformContactFromApi) || [],
  };

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:202:31
Error: Property 'data' does not exist on type '{}'. 
  const raw = await api.get(`api/v1/accounts/${accountId}/contacts/${contactId}`).json();
  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:222:31
Error: Property 'data' does not exist on type '{}'. 

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:257:33
Error: Property 'data' does not exist on type '{}'. 

    const contactPayload = raw?.data ?? raw;
    return transformContactFromApi(contactPayload);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:269:31
Error: Property 'data' does not exist on type '{}'. 

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:294:31
Error: Property 'data' does not exist on type '{}'. 

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/api/contacts.ts:324:31
Error: Property 'data' does not exist on type '{}'. 

  const contactPayload = raw?.data ?? raw;
  return transformContactFromApi(contactPayload);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/contacts.svelte.ts:21:86
Error: An import path can only end with a '.ts' extension when 'allowImportingTsExtensions' is enabled. 
} from '$lib/api/contacts';
import { BaseAction, QueryAction, MutationAction, createQuery, createMutation } from './base.svelte.ts';
import type { PaginatedResponse } from '$lib/api/types';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/contacts.svelte.ts:180:9
Error: Type '(variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact | { updatedAt: string; accountId: number; contactId: number; ... 20 more ...; conversations?: any[] | undefined; }' is not assignable to type '(variables: { accountId: number; contactId: number; } & UpdateContactParams) => Contact'.
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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/EmptyState.svelte:27:40
Warn: This reference only captures the initial value of `icon`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
  
  const IconComponent = iconComponents[icon];
</script>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/reports.svelte.ts:438:5
Error: Property 'overview' is missing in type '{ conversationMetrics: null; agentMetrics: never[]; teamMetrics: never[]; filters: { since: string; until: string; }; isLoading: false; error: null; }' but required in type 'ReportsState'. 
  reset() {
    this.state = {
      conversationMetrics: null,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmap.svelte:107:15
Warn: Self-closing HTML tags for non-void elements are ambiguous — use `<div ...></div>` rather than `<div ... />`
https://svelte.dev/e/element_invalid_self_closing_tag (svelte)
            {#each row.data as data}
              <div
                class="h-8 rounded-sm cursor-pointer transition-all duration-150 hover:scale-105 {getHeatmapClass(data.value)}"
                onmouseenter={(e) => handleCellHover(e, data.value)}
                onmouseleave={handleCellLeave}
                role="gridcell"
                tabindex="0"
                aria-label="{data.value} conversations at {new Date(data.timestamp * 1000).getHours()}:00"
              />
            {/each}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmap.svelte:121:7
Warn: Self-closing HTML tags for non-void elements are ambiguous — use `<div ...></div>` rather than `<div ... />`
https://svelte.dev/e/element_invalid_self_closing_tag (svelte)
      <!-- Spacer -->
      <div />

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmap.svelte:40:43
Error: Argument of type '() => { dateKey: string; data: HeatmapData[]; dataHash: string; }[]' is not assignable to parameter of type 'HeatmapRow[]'. (ts)
  // Process heatmap data into rows
  const dataRows = $derived<HeatmapRow[]>(() => {
    const groupedData = groupHeatmapByDay(heatmapData);

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.svelte:32:16
Error: Parameter 'details' implicitly has an 'any' type. (ts)
    initialValue: value || '',
    onchange: (details) => {
      // details not used, relying on reactive getters

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.svelte:54:45
Error: Parameter 'c' implicitly has an 'any' type. (ts)
    if (country && picker.selectedCountry && picker.selectedCountry.code !== country) {
      const found = picker.countryList.find(c => c.code === country);
      if (found) {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.svelte:66:47
Error: Parameter 'c' implicitly has an 'any' type. (ts)
    // cmdk/bits-ui often normalizes values to lowercase
    const selected = picker.countryList.find((c) => 
      c.name && c.name.toLowerCase() === currentValue.toLowerCase()

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/phone-input/phone-input.svelte:107:19
Error: Type '(currentValue: string) => void' is not assignable to type '() => void'.
  Target signature provides too few arguments. Expected 1 or more, but got 0. (ts)
                  value={c.name}
                  onSelect={handleSelect}
                >

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/contact-form/contact-form.svelte:20:44
Warn: This reference only captures the initial value of `contact`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)

  let form = $state(extractContactFormData(contact));
  let phoneCountry = $state('US');

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/contact-form/contact-form.svelte:197:19
Warn: `<svelte:component>` is deprecated in runes mode — components are dynamic by default
https://svelte.dev/e/svelte_component_deprecated (svelte)
                <div class="absolute left-3 text-muted-foreground flex items-center justify-center">
                  <svelte:component this={network.icon} class="h-4 w-4" />
                </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/contact-form/contact-form.svelte:227:15
Warn: `<svelte:component>` is deprecated in runes mode — components are dynamic by default
https://svelte.dev/e/svelte_component_deprecated (svelte)
            >
              <svelte:component this={network.icon} class="h-4 w-4" />
              <span>Add {network.label}</span>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/contact-form/contact-form.svelte:112:44
Error: Property 'name' does not exist on type '{ firstName: string; lastName: string; email: string; phone: string; company: string; city: string; countryCode: string; description: any; blocked: boolean; socialProfiles: any; }'. (ts)
      {#if avatarPreview}
        <img src={avatarPreview} alt={form.name || 'avatar'} />
      {:else}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-note/contact-note-item.svelte:23:13
Error: Object literal may only specify known properties, and '"size"' does not exist in type 'Omit<{ delayMs?: number | undefined; loadingStatus?: AvatarImageLoadingStatus | undefined; onLoadingStatusChange?: OnChangeFn<AvatarImageLoadingStatus> | undefined; }, "child" | "children"> & { ...; } & Without<...>'. (ts)
  <div class="flex items-start gap-3">
    <Avatar size="sm">
      <AvatarImage src={author.avatar} alt={author.name} />

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/custom-attributes/DateAttributeInput.svelte:92:12
Error: Type 'DateValue | undefined' is not assignable to type 'DateValue[] | undefined'.
  Type 'CalendarDate' is missing the following properties from type 'DateValue[]': length, pop, push, concat, and 35 more. (ts)
    <Calendar
      bind:value={dateValue}
      {disabled}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/help-center/article-editor/article-editor.svelte:213:16
Error: Type '"default" | "outline-solid"' is not assignable to type '"default" | "destructive" | "outline" | "secondary" | undefined'.
  Type '"outline-solid"' is not assignable to type '"default" | "destructive" | "outline" | "secondary" | undefined'. (ts)
      <div class="flex gap-2">
        <Badge variant={article.status === 'draft' ? 'default' : 'outline-solid'}>
          {article.status}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/pagination/pagination-footer.svelte:84:11
Error: Type '"default" | "outline-solid"' is not assignable to type '"default" | "link" | "destructive" | "outline" | "secondary" | "ghost" | undefined'.
  Type '"outline-solid"' is not assignable to type '"default" | "link" | "destructive" | "outline" | "secondary" | "ghost" | undefined'. (ts)
        <Button
          variant={currentPage === page ? 'default' : 'outline-solid'}
          size="sm"

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/toggle-group/toggle-group.svelte:27:3
Warn: This reference only captures the initial value of `variant`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
	setToggleGroupCtx({
		variant,
		size,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/toggle-group/toggle-group.svelte:28:3
Warn: This reference only captures the initial value of `size`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
		variant,
		size,
	});

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/composables/useLiveRefresh.svelte.ts:51:7
Error: Type 'Timeout' is not assignable to type 'number'. 
    function scheduleNext() {
      timeoutId = setTimeout(async () => {
        if (!isActive) return; // Check if still active

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/conversations.svelte.ts:543:20
Error: Property 'created_at' does not exist on type 'Message'. Did you mean 'createdAt'? 
      const lastMessage = allMessages.sort((a, b) => 
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      )[0];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/conversations.svelte.ts:543:55
Error: Property 'created_at' does not exist on type 'Message'. Did you mean 'createdAt'? 
      const lastMessage = allMessages.sort((a, b) => 
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      )[0];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/conversations.svelte.ts:598:9
Error: Type 'string' is not assignable to type 'number'. 
        conversation.unreadCount = 0;
        conversation.agentLastSeenAt = new Date().toISOString();
      }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/conversations.svelte.ts:605:27
Error: Property 'typingUsers' does not exist on type 'Conversation'. 
      if (conversation) {
        if (!conversation.typingUsers) {
          (conversation as any).typingUsers = [];

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/inboxes.svelte.ts:494:72
Error: Property 'inboxesCount' does not exist on type 'InboxesStore'. 
    // Log successful revalidation
    console.log(`Inboxes store revalidated successfully. Count: ${this.inboxesCount}`);
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/labels.svelte.ts:136:37
Error: Expected 2 arguments, but got 1. 
    try {
      const label = await labelsAPI.getLabel(labelId);
      this.addOrUpdateLabel(label);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/labels.svelte.ts:209:23
Error: Expected 2 arguments, but got 1. 
    try {
      await labelsAPI.deleteLabel(labelId);
      return true;

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/stores/notifications.svelte.ts:385:7
Error: Object literal may only specify known properties, and 'secondaryActor' does not exist in type 'Notification'. 
      primaryActor: conversation,
      secondaryActor: message,
      accountId: conversation.account_id,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/utils/campaign-helper.ts:26:16
Error: Cannot find name 'URLPattern'. Did you mean 'urlPattern'? 
    // Use URLPattern for matching (with polyfill support)
    if (typeof URLPattern !== 'undefined') {
      const pattern = new URLPattern(updatedUrlPattern);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/utils/campaign-helper.ts:27:27
Error: Cannot find name 'URLPattern'. Did you mean 'pattern'? 
    if (typeof URLPattern !== 'undefined') {
      const pattern = new URLPattern(updatedUrlPattern);
      return pattern.test(locationObj.toString());

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/client.ts:157:25
Error: Property 'state' is private and only accessible within class 'WebSocketStore'. 
    return {
      state: this.store.state,
      error: this.store.error,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/websocket/client.ts:171:25
Error: Argument of type 'null' is not assignable to parameter of type 'string'. 
    this.store.setState('connected');
    this.store.setError(null);

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/widget/api/messages.ts:127:29
Error: Property 'referrerURL' does not exist on type 'Window & typeof globalThis'. 
        timestamp: new Date().toString(),
        referer_url: window.referrerURL || '',
      }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/widget/websocket/client.ts:143:5
Error: Type 'number | boolean | null' is not assignable to type 'boolean'.
  Type 'null' is not assignable to type 'boolean'. 
    const { conversation_id: conversationId } = message;
    return this.activeConversationId && conversationId !== this.activeConversationId;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/widget/websocket/client.ts:172:44
Error: Argument of type 'WidgetMessage' is not assignable to parameter of type 'Message'.
  Types of property 'messageType' are incompatible.
    Type 'number' is not assignable to type '0 | 2 | 1'. 
      newMessages.forEach(message => {
        widgetConversationStore.addMessage(message);
      });

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/widget/websocket/client.ts:369:29
Error: Expected 2 arguments, but got 1. 

    widgetConversationStore.updateMessage(data);
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:391:15
Error: 'onsubmit|preventDefault' is not a valid attribute name
https://svelte.dev/e/attribute_invalid_name (svelte)
        
        <form onsubmit|preventDefault={handleCreateContact}>
          <div class="mb-4">

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:14:37
Error: An import path can only end with a '.ts' extension when 'allowImportingTsExtensions' is enabled. (ts)
  import { page } from '$app/stores';
  import { useContactActions } from '../contacts.svelte.ts';
  import type { Contact, CreateContactParams } from '$lib/api/contacts';

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:391:15
Error: Object literal may only specify known properties, and '"onsubmit|preventDefault"' does not exist in type 'HTMLProps<"form", HTMLAttributes<any>>'. (ts)
        
        <form onsubmit|preventDefault={handleCreateContact}>
          <div class="mb-4">

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/actions/examples/ContactsPage.svelte:429:38
Error: Property 'company' does not exist on type 'CreateContactParams'. (ts)
            <input
              bind:value={createForm.company}
              type="text"

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ConfirmDialog.svelte:48:16
Error: Type '"default" | "link" | "destructive" | "secondary" | "ghost" | "outline-solid"' is not assignable to type '"default" | "link" | "destructive" | "outline" | "secondary" | "ghost" | undefined'.
  Type '"outline-solid"' is not assignable to type '"default" | "link" | "destructive" | "outline" | "secondary" | "ghost" | undefined'. (ts)
      </Button>
      <Button {variant} onclick={(e: MouseEvent) => handleConfirm()}>
        {confirmText}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/SidebarAccountSwitcher.svelte:7:17
Error: '"svelte/elements"' has no exported member named 'HTMLDivAttributes'. Did you mean 'HTMLAttributes'? (ts)
  import { navigate } from '$lib/routing/navigation';
  import type { HTMLDivAttributes } from 'svelte/elements';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/SidebarAccountSwitcher.svelte:53:42
Error: Property 'avatarUrl' does not exist on type 'UserAccount'. (ts)
            <Avatar.Root class="h-6 w-6 rounded-lg">
              <Avatar.Image src={account.avatarUrl || ''} alt={account.name} />
              <Avatar.Fallback class="rounded-lg">

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/SidebarMenuItem.svelte:73:17
Warn: `<svelte:self>` is deprecated — use self-imports (e.g. `import Self from './Self.svelte'`) instead
https://svelte.dev/e/svelte_self_deprecated (svelte)
              {#each item.children as child (child.id)}
                <svelte:self item={child} sub={true} />
              {/each}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/SidebarMenuItem.svelte:116:19
Warn: `<svelte:self>` is deprecated — use self-imports (e.g. `import Self from './Self.svelte'`) instead
https://svelte.dev/e/svelte_self_deprecated (svelte)
                {#each item.children as child (child.id)}
                  <svelte:self item={child} sub={true} />
                {/each}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:562:11
Warn: Self-closing HTML tags for non-void elements are ambiguous — use `<div ...></div>` rather than `<div ... />`
https://svelte.dev/e/element_invalid_self_closing_tag (svelte)
          </div>
          <div class="flex-shrink-0 w-px h-3 bg-border" />
          <SidebarAccountSwitcher class="flex-1 min-w-0" />

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:125:11
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; href: string; activeOn: string[]; }[]; }' but required in type 'NavigationItem'. (ts)
          },
          {
            id: 'custom-views',
            label: 'Custom Views',
            icon: 'folder',
            children: customViewsStore.conversationViews.map(view => ({
              id: `view-${view.id}`,
              label: view.name,
              href: `/app/accounts/${accountId}/conversations/custom_view/${view.id}`,
              activeOn: [
                `/app/accounts/${accountId}/conversations/custom_view/${view.id}`,
              ],
            })),
          },
          {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:138:11
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; icon: string; href: string; activeOn: string[]; }[]; }' but required in type 'NavigationItem'. (ts)
          },
          {
            id: 'channels',
            label: 'Inboxes',
            icon: 'inbox',
            children: inboxesStore.sortedInboxes.map(inbox => ({
              id: `inbox-${inbox.id}`,
              label: inbox.name,
              icon: getChannelIcon(inbox.channelType),
              href: `/app/accounts/${accountId}/conversations/inbox/${inbox.id}`,
              activeOn: [
                `/app/accounts/${accountId}/conversations/inbox/${inbox.id}`,
              ],
            })),
          },
          {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:152:11
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; href: string; activeOn: string[]; }[]; }' but required in type 'NavigationItem'. (ts)
          },
          {
            id: 'teams',
            label: 'Teams',
            icon: 'users',
            children: teamsStore.myTeams.map(team => ({
              id: `team-${team.id}`,
              label: team.name,
              href: `/app/accounts/${accountId}/conversations/team/${team.id}`,
              activeOn: [
                `/app/accounts/${accountId}/conversations/team/${team.id}`,
              ],
            })),
          },
          {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:165:11
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; href: string; activeOn: string[]; }[]; }' but required in type 'NavigationItem'. (ts)
          },
          {
            id: 'folders',
            label: 'Labels',
            icon: 'tags',
            children: labelsStore.sidebarLabels.map(label => ({
              id: `label-${label.id}`,
              label: label.title,
              href: `/app/accounts/${accountId}/conversations/label/${encodeURIComponent(
                label.title
              )}`,
              activeOn: [
                `/app/accounts/${accountId}/conversations/label/${encodeURIComponent(
                  label.title
                )}`,
              ],
            })),
          },
        ],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:226:11
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; icon: string; iconColor: string; href: string; activeOn: string[]; }[]; permission: string; }' but required in type 'NavigationItem'. (ts)
          // Labels
          {
            id: 'contacts-labels',
            label: 'Labels',
            icon: 'tags',
            children: labelsStore.sidebarLabels.map(label => ({
              id: `contact-label-${label.title}`,
              label: label.title,
              icon: 'tag',
              iconColor: label.color,
              href: `/app/accounts/${accountId}/contacts/labels/${encodeURIComponent(label.title)}`,
              activeOn: [
                `/app/accounts/${accountId}/contacts/labels/${encodeURIComponent(label.title)}`,
              ],
            })),
            permission: 'contact_manage',
          },
          {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:252:7
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; href: string; activeOn: string[]; permission: string; }[]; }' but required in type 'NavigationItem'. (ts)
      },
      {
        id: 'reports',
          label: 'Reports',
        icon: 'chart-spline',
        children: [
          // The following items map to the report pages present in Vue
          // Use a safe translation helper to avoid calling the formatter before
          // the initial locale is set (which throws in svelte-i18n).
          {
            id: 'reports-overview',
            label: safeT("SIDEBAR.REPORTS_OVERVIEW", 'Overview'),
            href: `/app/accounts/${accountId}/reports`,
            activeOn: [`/app/accounts/${accountId}/reports`],
            permission: 'report_manage',
          },
          {
            id: 'reports-conversation',
            label: safeT("SIDEBAR.REPORTS_CONVERSATION", 'Conversation'),
            href: `/app/accounts/${accountId}/reports/conversations`,
            activeOn: [`/app/accounts/${accountId}/reports/conversations`],
            permission: 'report_manage',
          },
          {
            id: 'reports-agent',
              label: safeT("SIDEBAR.REPORTS_AGENT", 'Agent Reports'),
            href: `/app/accounts/${accountId}/reports/agent`,
            activeOn: [`/app/accounts/${accountId}/reports/agent`],
            permission: 'report_manage',
          },
          {
            id: 'reports-label',
              label: safeT("SIDEBAR.REPORTS_LABEL", 'Label Reports'),
            href: `/app/accounts/${accountId}/reports/label`,
            activeOn: [`/app/accounts/${accountId}/reports/label`],
            permission: 'report_manage',
          },
          {
            id: 'reports-inbox',
              label: safeT("SIDEBAR.REPORTS_INBOX", 'Inbox Reports'),
            href: `/app/accounts/${accountId}/reports/inbox`,
            activeOn: [`/app/accounts/${accountId}/reports/inbox`],
            permission: 'report_manage',
          },
          {
            id: 'reports-team',
              label: safeT("SIDEBAR.REPORTS_TEAM", 'Team Reports'),
            href: `/app/accounts/${accountId}/reports/team`,
            activeOn: [`/app/accounts/${accountId}/reports/team`],
            permission: 'report_manage',
          },
            {
              id: 'reports-csat',
              label: safeT("SIDEBAR.CSAT", 'CSAT'),
              href: `/app/accounts/${accountId}/reports/csat`,
              activeOn: [`/app/accounts/${accountId}/reports/csat`],
              permission: 'report_manage',
            },
            {
              id: 'reports-sla',
              label: safeT("SIDEBAR.REPORTS_SLA", 'SLA'),
              href: `/app/accounts/${accountId}/reports/sla`,
              activeOn: [`/app/accounts/${accountId}/reports/sla`],
              permission: 'report_manage',
            },
            {
              id: 'reports-bot',
              label: safeT("SIDEBAR.REPORTS_BOT", 'Bot'),
              href: `/app/accounts/${accountId}/reports/bot`,
              activeOn: [`/app/accounts/${accountId}/reports/bot`],
              permission: 'report_manage',
            },
        ],
      },
      {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:325:7
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; href: string; activeOn: string[]; permission: string; }[]; }' but required in type 'NavigationItem'. (ts)
      },
      {
        id: 'campaigns',
        label: 'Campaigns',
        icon: 'megaphone',
        children: [
          {
            id: 'campaigns-livechat',
            label: 'Live chat',
            href: `/app/accounts/${accountId}/campaigns/livechat`,
            activeOn: [`/app/accounts/${accountId}/campaigns/livechat`],
            permission: 'administrator',
          },
          {
            id: 'campaigns-sms',
            label: 'SMS',
            href: `/app/accounts/${accountId}/campaigns/sms`,
            activeOn: [`/app/accounts/${accountId}/campaigns/sms`],
            permission: 'administrator',
          },
          {
            id: 'campaigns-whatsapp',
            label: 'WhatsApp',
            href: `/app/accounts/${accountId}/campaigns/whatsapp`,
            activeOn: [`/app/accounts/${accountId}/campaigns/whatsapp`],
            permission: 'administrator',
          },
        ],
      },
      {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:353:7
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; href: string; activeOn: string[]; permission: string; }[]; }' but required in type 'NavigationItem'. (ts)
      },
      {
        id: 'portals',
        label: 'Help Center',
        icon: 'library-big',
        children: [
          {
            id: 'portal-articles',
            label: 'Articles',
            href: `/app/accounts/${accountId}/portals/articles`,
            activeOn: [`/app/accounts/${accountId}/portals/articles`],
            permission: 'knowledge_base_manage',
          },
          {
            id: 'portal-categories',
            label: 'Categories',
            href: `/app/accounts/${accountId}/portals/categories`,
            activeOn: [`/app/accounts/${accountId}/portals/categories`],
            permission: 'knowledge_base_manage',
          },
          {
            id: 'portal-locales',
            label: 'Locales',
            href: `/app/accounts/${accountId}/portals/locales`,
            activeOn: [`/app/accounts/${accountId}/portals/locales`],
            permission: 'knowledge_base_manage',
          },
          {
            id: 'portal-settings',
            label: 'Settings',
            href: `/app/accounts/${accountId}/portals/settings`,
            activeOn: [`/app/accounts/${accountId}/portals/settings`],
            permission: 'knowledge_base_manage',
          },
        ],
      },
      {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/AppSidebar.svelte:388:7
Error: Property 'href' is missing in type '{ id: string; label: string; icon: string; children: { id: string; label: string; icon: string; href: string; activeOn: string[]; permission: string; }[]; }' but required in type 'NavigationItem'. (ts)
      },
      {
        id: 'settings',
        label: 'Settings',
        icon: 'bolt',
        children: [
          {
            id: 'settings-account',
            label: 'Account Settings',
            icon: 'briefcase',
            href: `/app/accounts/${accountId}/settings/account`,
            activeOn: [`/app/accounts/${accountId}/settings/account`],
            permission: 'administrator',
          },
          {
            id: 'settings-agents',
            label: 'Agents',
            icon: 'square-user',
            href: `/app/accounts/${accountId}/settings/agents`,
            activeOn: [`/app/accounts/${accountId}/settings/agents`],
            permission: 'administrator',
          },
          {
            id: 'settings-teams',
            label: 'Teams',
            icon: 'users',
            href: `/app/accounts/${accountId}/settings/teams`,
            activeOn: [`/app/accounts/${accountId}/settings/teams`],
            permission: 'administrator',
          },
          {
            id: 'settings-assignment',
            label: 'Agent Assignment',
            icon: 'user-cog',
            href: `/app/accounts/${accountId}/settings/assignment`,
            activeOn: [`/app/accounts/${accountId}/settings/assignment`],
            permission: 'administrator',
          },
          {
            id: 'settings-inboxes',
            label: 'Inboxes',
            icon: 'inbox',
            href: `/app/accounts/${accountId}/settings/inboxes`,
            activeOn: [`/app/accounts/${accountId}/settings/inboxes`],
            permission: 'administrator',
          },
          {
            id: 'settings-labels',
            label: 'Labels',
            icon: 'tags',
            href: `/app/accounts/${accountId}/settings/labels`,
            activeOn: [`/app/accounts/${accountId}/settings/labels`],
            permission: 'administrator',
          },
          {
            id: 'settings-attributes',
            label: 'Custom Attributes',
            icon: 'code',
            href: `/app/accounts/${accountId}/settings/attributes`,
            activeOn: [`/app/accounts/${accountId}/settings/attributes`],
            permission: 'administrator',
          },
          {
            id: 'settings-automation',
            label: 'Automation',
            icon: 'workflow',
            href: `/app/accounts/${accountId}/settings/automation`,
            activeOn: [`/app/accounts/${accountId}/settings/automation`],
            permission: 'administrator',
          },
          {
            id: 'settings-agent-bots',
            label: 'Agent Bots',
            icon: 'bot',
            href: `/app/accounts/${accountId}/settings/agent-bots`,
            activeOn: [`/app/accounts/${accountId}/settings/agent-bots`],
            permission: 'administrator',
          },
          {
            id: 'settings-macros',
            label: 'Macros',
            icon: 'toy-brick',
            href: `/app/accounts/${accountId}/settings/macros`,
            activeOn: [`/app/accounts/${accountId}/settings/macros`],
            permission: 'administrator',
          },
          {
            id: 'settings-canned',
            label: 'Canned Responses',
            icon: 'message-square-quote',
            href: `/app/accounts/${accountId}/settings/canned`,
            activeOn: [`/app/accounts/${accountId}/settings/canned`],
            permission: 'administrator',
          },
          {
            id: 'settings-integrations',
            label: 'Integrations',
            icon: 'blocks',
            href: `/app/accounts/${accountId}/settings/integrations`,
            activeOn: [`/app/accounts/${accountId}/settings/integrations`],
            permission: 'administrator',
          },
          {
            id: 'settings-audit',
            label: 'Audit Logs',
            icon: 'briefcase',
            href: `/app/accounts/${accountId}/settings/audit`,
            activeOn: [`/app/accounts/${accountId}/settings/audit`],
            permission: 'administrator',
          },
          {
            id: 'settings-roles',
            label: 'Custom Roles',
            icon: 'shield-plus',
            href: `/app/accounts/${accountId}/settings/roles`,
            activeOn: [`/app/accounts/${accountId}/settings/roles`],
            permission: 'administrator',
          },
          {
            id: 'settings-sla',
            label: 'SLA',
            icon: 'clock-alert',
            href: `/app/accounts/${accountId}/settings/sla`,
            activeOn: [`/app/accounts/${accountId}/settings/sla`],
            permission: 'administrator',
          },
          {
            id: 'settings-security',
            label: 'Security',
            icon: 'shield',
            href: `/app/accounts/${accountId}/settings/security`,
            activeOn: [`/app/accounts/${accountId}/settings/security`],
            permission: 'administrator',
          },
          {
            id: 'settings-billing',
            label: 'Billing',
            icon: 'credit-card',
            href: `/app/accounts/${accountId}/settings/billing`,
            activeOn: [`/app/accounts/${accountId}/settings/billing`],
            permission: 'administrator',
          },
        ],
      },
    ])

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/layout/MobileSidebarLauncher.svelte:32:9
Warn: Self-closing HTML tags for non-void elements are ambiguous — use `<span ...></span>` rather than `<span ... />`
https://svelte.dev/e/element_invalid_self_closing_tag (svelte)
      >
        <span class="i-lucide-menu text-lg" />
      </Button>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/macros/MacrosList.svelte:111:24
Error: Type '"default" | "secondary" | "outline-solid"' is not assignable to type '"default" | "destructive" | "outline" | "secondary" | undefined'.
  Type '"outline-solid"' is not assignable to type '"default" | "destructive" | "outline" | "secondary" | undefined'. (ts)
                <h3 class="text-lg font-semibold">{macro.name}</h3>
                <Badge variant={getVisibilityColor(macro.visibility)}>
                  {@const VisibilityIcon = getVisibilityIcon(macro.visibility)}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/navigation/FilterChips.svelte:21:7
Error: Type '"default" | "outline-solid"' is not assignable to type '"default" | "link" | "destructive" | "outline" | "secondary" | "ghost" | undefined'.
  Type '"outline-solid"' is not assignable to type '"default" | "link" | "destructive" | "outline" | "secondary" | "ghost" | undefined'. (ts)
    <Button
      variant={filter.isActive ? 'default' : 'outline-solid'}
      size="sm"

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/notifications/NotificationBell.svelte:18:26
Error: Expected 1-2 arguments, but got 0. (ts)
    if (open && notificationsStore.all.length === 0) {
      notificationsStore.fetchNotifications();
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/notifications/NotificationBell.svelte:23:30
Error: Expected 1 arguments, but got 0. (ts)
  async function handleMarkAllRead() {
    await notificationsStore.markAllAsRead();
    toast.success('All notifications marked as read');

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportMetricCard.svelte:47:11
Warn: `<svelte:component>` is deprecated in runes mode — components are dynamic by default
https://svelte.dev/e/svelte_component_deprecated (svelte)
        <div class="flex items-center gap-1 {trendColor}">
          <svelte:component this={TrendIcon} class="h-4 w-4" />
          <span class="text-sm font-medium">{Math.abs(trend)}%</span>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportMetricCard.svelte:4:30
Error: Cannot find module '$lib/utils/timeHelper' or its corresponding type declarations. (ts)
  import { TrendingUp, TrendingDown } from 'lucide-svelte';
  import { formatTime } from '$lib/utils/timeHelper';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatMetrics.svelte:2:29
Error: Cannot find module '$lib/stores/csat.svelte' or its corresponding type declarations. (ts)
<script lang="ts">
  import { csatStore } from '$lib/stores/csat.svelte';
  import ReportMetricCard from '../shared/ReportMetricCard.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatMetrics.svelte:38:26
Error: Object literal may only specify known properties, and '"height"' does not exist in type 'Props'. (ts)
      {#each Array(3) as _}
        <LoadingSkeleton height="100px" />
      {/each}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatTable.svelte:3:29
Error: Cannot find module '$lib/stores/csat.svelte' or its corresponding type declarations. (ts)
  import { createEventDispatcher } from 'svelte';
  import { csatStore } from '$lib/stores/csat.svelte';
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '$lib/components/ui/table';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/csat/CsatTable.svelte:45:22
Error: Object literal may only specify known properties, and '"height"' does not exist in type 'Props'. (ts)
  {#if isLoading}
    <LoadingSkeleton height="400px" />
  {:else if responses && responses.length > 0}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/LiveBadge.svelte:21:3
Warn: Self-closing HTML tags for non-void elements are ambiguous — use `<span ...></span>` rather than `<span ... />`
https://svelte.dev/e/element_invalid_self_closing_tag (svelte)
<span class="flex flex-row items-center rounded bg-teal-100 dark:bg-teal-900/30 {sizeClasses[size]}">
  <span 
    class="bg-teal-600 dark:bg-teal-400 rounded-full mr-1 rtl:mr-0 rtl:ml-1 {dotSizeClasses[size]} {pulse ? 'animate-pulse' : ''}"
  />
  <span class="text-teal-700 dark:text-teal-300 font-medium">

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/HeatmapDateRangeSelector.svelte:154:29
Error: Object literal may only specify known properties, and '"asChild"' does not exist in type 'Omit<{ disabled?: boolean | null | undefined; }, "child" | "children"> & { child?: Snippet<[{ props: Record<string, unknown>; }]> | undefined; children?: Snippet<[]> | undefined; style?: any; ref?: HTMLElement | ... 1 more ... | undefined; } & Without<...>'. (ts)
    <DropdownMenu.Root bind:open={showRangeDropdown}>
      <DropdownMenu.Trigger asChild>
        <Button

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:214:6
Error: Object literal may only specify known properties, and 'title' does not exist in type 'Props'. (ts)
  <MetricCard 
    {title}
    isLive={true}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:222:11
Error: Cannot use 'bind:' with this property. It is declared as non-bindable inside the component.
To mark a property as bindable: 'let { from = $bindable() } = $props()' (ts)
        <HeatmapDateRangeSelector
          bind:from={selectedFrom}
          bind:to={selectedTo}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:223:11
Error: Cannot use 'bind:' with this property. It is declared as non-bindable inside the component.
To mark a property as bindable: 'let { to = $bindable() } = $props()' (ts)
          bind:from={selectedFrom}
          bind:to={selectedTo}
          bind:daysNum={selectedDaysBefore}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:224:11
Error: Cannot use 'bind:' with this property. It is declared as non-bindable inside the component.
To mark a property as bindable: 'let { daysNum = $bindable() } = $props()' (ts)
          bind:to={selectedTo}
          bind:daysNum={selectedDaysBefore}
          onRangeTypeChange={handleRangeTypeChange}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:231:33
Error: Object literal may only specify known properties, and '"asChild"' does not exist in type 'Omit<{ disabled?: boolean | null | undefined; }, "child" | "children"> & { child?: Snippet<[{ props: Record<string, unknown>; }]> | undefined; children?: Snippet<[]> | undefined; style?: any; ref?: HTMLElement | ... 1 more ... | undefined; } & Without<...>'. (ts)
        <DropdownMenu.Root bind:open={showInboxDropdown}>
          <DropdownMenu.Trigger asChild>
            <Button

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/overview/AgentTable.svelte:211:15
Warn: Using `on:change` to listen to the change event is deprecated. Use the event attribute `onchange` instead
https://svelte.dev/e/event_directive_deprecated (svelte)
              bind:value={pageSize}
              on:change={(e) => handlePageSizeChange(parseInt(e.currentTarget.value))}
              class="text-sm border border-slate-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/overview/StatsLiveReportsContainer.svelte:85:35
Error: Object literal may only specify known properties, and '"asChild"' does not exist in type 'Omit<{ disabled?: boolean | null | undefined; }, "child" | "children"> & { child?: Snippet<[{ props: Record<string, unknown>; }]> | undefined; children?: Snippet<[]> | undefined; style?: any; ref?: HTMLElement | ... 1 more ... | undefined; } & Without<...>'. (ts)
          <DropdownMenu.Root bind:open={showTeamDropdown}>
            <DropdownMenu.Trigger asChild>
              <Button

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/overview/TeamTable.svelte:172:15
Warn: Using `on:change` to listen to the change event is deprecated. Use the event attribute `onchange` instead
https://svelte.dev/e/event_directive_deprecated (svelte)
              bind:value={pageSize}
              on:change={(e) => handlePageSizeChange(parseInt(e.currentTarget.value))}
              class="text-sm border border-slate-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportChart.svelte:110:22
Error: Object literal may only specify known properties, and '"height"' does not exist in type 'Props'. (ts)
  {#if isLoading}
    <LoadingSkeleton height="300px" />
  {:else if chartData && chartData.data.length > 0}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportContainer.svelte:21:48
Error: Property 'getData' does not exist on type 'ReportsStore'. (ts)

  const accountSummary = $derived(reportsStore.getData(accountSummaryKey));
  const isFetchingSummary = $derived(reportsStore.getUIFlag(summaryFetchingKey));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportContainer.svelte:22:51
Error: Property 'getUIFlag' does not exist on type 'ReportsStore'. (ts)
  const accountSummary = $derived(reportsStore.getData(accountSummaryKey));
  const isFetchingSummary = $derived(reportsStore.getUIFlag(summaryFetchingKey));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportContainer.svelte:82:26
Error: Object literal may only specify known properties, and '"height"' does not exist in type 'Props'. (ts)
      {#each Array(4) as _}
        <LoadingSkeleton height="120px" />
      {/each}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:6:31
Error: Cannot find module '$lib/components/ui/date-range-picker/DateRangePicker.svelte' or its corresponding type declarations. (ts)
  import { Switch } from '$lib/components/ui/switch';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:7:35
Error: Cannot find module '$lib/constants/reports' or its corresponding type declarations. (ts)
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:39:19
Error: 'filter' is of type 'unknown'. (ts)
    const selected = Object.values(GROUP_BY_FILTER).find(
      (filter) => filter.id?.toString() === value
    );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:85:35
Error: 'filter' is of type 'unknown'. (ts)
            {#each Object.values(GROUP_BY_FILTER) as filter}
              <Select.Item value={filter.id?.toString()} label={filter.label}>
                {filter.label}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:85:65
Error: 'filter' is of type 'unknown'. (ts)
            {#each Object.values(GROUP_BY_FILTER) as filter}
              <Select.Item value={filter.id?.toString()} label={filter.label}>
                {filter.label}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilterSelector.svelte:86:18
Error: 'filter' is of type 'unknown'. (ts)
              <Select.Item value={filter.id?.toString()} label={filter.label}>
                {filter.label}
              </Select.Item>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilters.svelte:26:36
Warn: This reference only captures the initial value of `currentFilter`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)

  let selectedFilterValue = $state(currentFilter?.id?.toString() || '');
  let selectedGroupByValue = $state(selectedGroupByFilter?.id?.toString() || '');

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilters.svelte:27:37
Warn: This reference only captures the initial value of `selectedGroupByFilter`. Did you mean to reference it inside a derived instead?
https://svelte.dev/e/state_referenced_locally (svelte)
  let selectedFilterValue = $state(currentFilter?.id?.toString() || '');
  let selectedGroupByValue = $state(selectedGroupByFilter?.id?.toString() || '');
  let businessHoursEnabled = $state(false);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportFilters.svelte:6:31
Error: Cannot find module '$lib/components/ui/date-range-picker/DateRangePicker.svelte' or its corresponding type declarations. (ts)
  import { Switch } from '$lib/components/ui/switch';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/ReportHeader.svelte:35:7
Warn: Using `<slot>` to render parent content is deprecated. Use `{@render ...}` tags instead
https://svelte.dev/e/slot_element_deprecated (svelte)
    <div class="flex-shrink-0">
      <slot />
    </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:52:31
Warn: This reference only captures the initial value of `selectedItem`. Did you mean to reference it inside a closure instead?
https://svelte.dev/e/state_referenced_locally (svelte)
  let to = $state(0);
  let selectedFilter = $state(selectedItem);
  let groupBy = $state(GROUP_BY_FILTER[1]);

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:9:35
Error: Cannot find module '$lib/constants/reports' or its corresponding type declarations. (ts)
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';
  import { generateFileName } from '$lib/utils/downloadHelper';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:10:36
Error: Cannot find module '$lib/utils/downloadHelper' or its corresponding type declarations. (ts)
  import { GROUP_BY_FILTER } from '$lib/constants/reports';
  import { generateFileName } from '$lib/utils/downloadHelper';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:60:30
Error: Property 'getFilterItems' does not exist on type 'ReportsStore'. (ts)
  const filterItemsList = $derived(
    getterKey ? reportsStore.getFilterItems(getterKey) : []
  );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:79:20
Error: Property 'dispatchAction' does not exist on type 'ReportsStore'. (ts)
    if (actionKey) {
      reportsStore.dispatchAction(actionKey);
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:85:20
Error: Property 'fetchAccountSummary' does not exist on type 'ReportsStore'. (ts)
    if (selectedFilter) {
      reportsStore.fetchAccountSummary({
        from,

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:100:28
Error: Property 'fetchAccountReport' does not exist on type 'ReportsStore'. Did you mean 'fetchAccountReports'? (ts)
      try {
        await reportsStore.fetchAccountReport({
          metric: reportKeys[key as keyof typeof reportKeys],

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/reports/shared/WootReports.svelte:126:20
Error: Property 'dispatchAction' does not exist on type 'ReportsStore'. (ts)
      const params = { from, to, fileName, businessHours };
      reportsStore.dispatchAction(dispatchMethods[type], params);
    }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:80:5
Warn: Unknown at rule @apply (css)
  .presence-indicator {
    @apply inline-flex items-center;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:84:5
Warn: Unknown at rule @apply (css)
  .presence-indicator__dot {
    @apply flex-shrink-0;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:32:12
Error: Type 'string' is not comparable to type '() => "online" | "offline" | "away"'. (ts)
    switch (status) {
      case 'online':
        return {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:38:12
Error: Type 'string' is not comparable to type '() => "online" | "offline" | "away"'. (ts)
        };
      case 'away':
        return {

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:44:12
Error: Type 'string' is not comparable to type '() => "online" | "offline" | "away"'. (ts)
        };
      case 'offline':
      default:

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:69:34
Error: Property 'variant' does not exist on type '() => { color: string; label: string; variant: "default"; } | { color: string; label: string; variant: "secondary"; } | { color: string; label: string; variant: "outline"; }'. (ts)
  {#if showLabel}
    <Badge variant={statusConfig.variant} class="flex items-center gap-1.5">
      <div class="presence-indicator__dot {sizeClasses} {statusConfig.color} rounded-full"></div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:70:71
Error: Property 'color' does not exist on type '() => { color: string; label: string; variant: "default"; } | { color: string; label: string; variant: "secondary"; } | { color: string; label: string; variant: "outline"; }'. (ts)
    <Badge variant={statusConfig.variant} class="flex items-center gap-1.5">
      <div class="presence-indicator__dot {sizeClasses} {statusConfig.color} rounded-full"></div>
      <span class="text-xs">{statusConfig.label}</span>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:71:43
Error: Property 'label' does not exist on type '() => { color: string; label: string; variant: "default"; } | { color: string; label: string; variant: "secondary"; } | { color: string; label: string; variant: "outline"; }'. (ts)
      <div class="presence-indicator__dot {sizeClasses} {statusConfig.color} rounded-full"></div>
      <span class="text-xs">{statusConfig.label}</span>
    </Badge>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/presence-indicator.svelte:74:69
Error: Property 'color' does not exist on type '() => { color: string; label: string; variant: "default"; } | { color: string; label: string; variant: "secondary"; } | { color: string; label: string; variant: "outline"; }'. (ts)
  {:else}
    <div class="presence-indicator__dot {sizeClasses} {statusConfig.color} rounded-full border-2 border-background"></div>
  {/if}

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:52:5
Warn: `<svelte:component>` is deprecated in runes mode — components are dynamic by default
https://svelte.dev/e/svelte_component_deprecated (svelte)
  <Badge {variant} class="flex items-center gap-1.5">
    <svelte:component this={icon} class="w-3 h-3 {iconClass}" />
    <span class="text-xs font-medium">

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:88:5
Warn: Unknown at rule @apply (css)
  .websocket-status {
    @apply flex flex-col gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:92:5
Warn: Unknown at rule @apply (css)
  .websocket-status__details {
    @apply flex flex-col gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:96:5
Warn: Unknown at rule @apply (css)
  .websocket-status__error {
    @apply flex items-center gap-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:101:5
Warn: Unknown at rule @apply (css)
  .websocket-status__subscriptions {
    @apply flex items-center;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/websocket-status.svelte:51:11
Error: Type '() => "default" | "outline" | "secondary" | "destructive"' is not assignable to type '"default" | "destructive" | "outline" | "secondary" | undefined'. (ts)
<div class="websocket-status {className}">
  <Badge {variant} class="flex items-center gap-1.5">
    <svelte:component this={icon} class="w-3 h-3 {iconClass}" />

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/contact-management/bulk-action-bar.svelte:129:28
Error: Object literal may only specify known properties, and '"asChild"' does not exist in type 'Omit<{}, "child" | "children"> & { child?: Snippet<[{ props: Record<string, unknown>; }]> | undefined; children?: Snippet<[]> | undefined; style?: any; ref?: HTMLElement | ... 1 more ... | undefined; } & Without<...>'. (ts)
        <Popover.Root bind:open={showLabelSelector}>
          <Popover.Trigger asChild>
            <Button

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/ui/select/select.svelte:15:38
Error: Type 'string | string[] | undefined' is not assignable to type 'string[] | undefined'.
  Type 'string' is not assignable to type 'string[]'. (ts)

<SelectPrimitive.Root bind:open bind:value { ...restProps } />

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:109:5
Warn: Unknown at rule @apply (css)
  .chat-bubble {
    @apply max-w-[85%] cursor-pointer p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:113:5
Warn: Unknown at rule @apply (css)
  .row--agent-block {
    @apply items-center flex text-left pb-2 text-xs;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:117:5
Warn: Unknown at rule @apply (css)
  .agent--name {
    @apply font-medium ml-1 text-gray-900;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:121:5
Warn: Unknown at rule @apply (css)
  .company--name {
    @apply text-gray-600 ml-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:125:5
Warn: Unknown at rule @apply (css)
  .message-content {
    @apply text-sm text-gray-800 leading-relaxed;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:129:5
Warn: Unknown at rule @apply (css)
  .message-content :global(br) {
    @apply block mb-1;
  }

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/lib/components/widget/CampaignMessage.svelte:88:11
Error: Object literal may only specify known properties, and '"src"' does not exist in type 'Omit<{ delayMs?: number | undefined; loadingStatus?: AvatarImageLoadingStatus | undefined; onLoadingStatusChange?: OnChangeFn<AvatarImageLoadingStatus> | undefined; }, "child" | "children"> & { ...; } & Without<...>'. (ts)
        <Avatar
          src={avatarUrl}
          size={20}

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:434:23
Warn: `<svelte:component>` is deprecated in runes mode — components are dynamic by default
https://svelte.dev/e/svelte_component_deprecated (svelte)
                    >
                      <svelte:component this={media.icon} class="h-5 w-5" />
                    </a>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:446:43
Error: Argument of type 'number' is not assignable to parameter of type 'string'. (ts)
                <Calendar class="h-4 w-4" />
                <span>Created {formatDate(contact.createdAt)}</span>
              </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:452:21
Error: Argument of type 'number | null' is not assignable to parameter of type 'string | null | undefined'.
  Type 'number' is not assignable to type 'string'. (ts)
                  >Last activity {formatRelativeTime(
                    contact.lastActivityAt
                  )}</span

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:565:80
Error: Type 'unknown' is not assignable to type 'string | null | undefined'. (ts)
                                    <span class="text-sm text-muted-foreground">{formatSocialLabel(sk)}:</span>
                                    <a class="text-sm text-blue-600 underline" href={String(sv).startsWith('http') ? sv : `https://${String(sv)}`} target="_blank" rel="noopener noreferrer">{sv}</a>
                                  </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:664:82
Error: Type 'unknown' is not assignable to type 'string | null | undefined'. (ts)
                                      <span class="text-sm text-muted-foreground">{formatSocialLabel(sk)}:</span>
                                      <a class="text-sm text-blue-600 underline" href={String(sv).startsWith('http') ? sv : `https://${String(sv)}`} target="_blank" rel="noopener noreferrer">{sv}</a>
                                    </div>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/bot/+page.svelte:4:26
Error: Cannot find module '$lib/components/reports/bot/BotMetrics.svelte' or its corresponding type declarations. (ts)
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import BotMetrics from '$lib/components/reports/bot/BotMetrics.svelte';
  import ReportFilterSelector from '$lib/components/reports/shared/ReportFilterSelector.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/bot/+page.svelte:8:35
Error: Cannot find module '$lib/constants/reports' or its corresponding type declarations. (ts)
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/conversation/+page.svelte:10:35
Error: Cannot find module '$lib/constants/reports' or its corresponding type declarations. (ts)
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';
  import { generateFileName } from '$lib/utils/downloadHelper';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/conversation/+page.svelte:11:36
Error: Cannot find module '$lib/utils/downloadHelper' or its corresponding type declarations. (ts)
  import { GROUP_BY_FILTER } from '$lib/constants/reports';
  import { generateFileName } from '$lib/utils/downloadHelper';

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/csat/+page.svelte:10:29
Error: Cannot find module '$lib/stores/csat.svelte' or its corresponding type declarations. (ts)
  import { Download } from 'lucide-svelte';
  import { csatStore } from '$lib/stores/csat.svelte';
  import { accountStore } from '$lib/stores/account.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/csat/+page.svelte:11:32
Error: Cannot find module '$lib/stores/account.svelte' or its corresponding type declarations. (ts)
  import { csatStore } from '$lib/stores/csat.svelte';
  import { accountStore } from '$lib/stores/account.svelte';
  import { generateFileName } from '$lib/utils/downloadHelper';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/csat/+page.svelte:12:36
Error: Cannot find module '$lib/utils/downloadHelper' or its corresponding type declarations. (ts)
  import { accountStore } from '$lib/stores/account.svelte';
  import { generateFileName } from '$lib/utils/downloadHelper';
  import { FEATURE_FLAGS } from '$lib/constants/featureFlags';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/csat/+page.svelte:13:33
Error: Cannot find module '$lib/constants/featureFlags' or its corresponding type declarations. (ts)
  import { generateFileName } from '$lib/utils/downloadHelper';
  import { FEATURE_FLAGS } from '$lib/constants/featureFlags';

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

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:9:35
Error: Cannot find module '$lib/stores/slaReports.svelte' or its corresponding type declarations. (ts)
  import { Download } from 'lucide-svelte';
  import { slaReportsStore } from '$lib/stores/slaReports.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:15:36
Error: Cannot find module '$lib/utils/downloadHelper' or its corresponding type declarations. (ts)
  import { slaStore } from '$lib/stores/sla.svelte';
  import { generateFileName } from '$lib/utils/downloadHelper';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:38:14
Error: Property 'fetchSLAs' does not exist on type 'SLAStore'. (ts)
    labelsStore.fetchLabels();
    slaStore.fetchSLAs();
    fetchSLAMetrics();

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountId.svelte:18:2
Error: Property 'headerActions' is missing in type '{ children: () => any; title: string; description: string; withBorder: true; }' but required in type '$$ComponentProps'. (ts)

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.ACCOUNT_ID.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/BuildInfo.svelte:8:66
Error: Property 'latest_chatwoot_version' does not exist on type 'UserAccount'. (ts)

  let latestChatwootVersion = $derived(authStore.currentAccount?.latest_chatwoot_version);
  let appVersion = $derived(globalConfig.get('appVersion'));

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:10:56
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. (ts)
  let currentAccount = $derived(authStore.currentAccount);
  let isMarkedForDeletion = $derived(!!currentAccount?.custom_attributes?.marked_for_deletion_at);
  let markedForDeletionDate = $derived(

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:12:21
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. (ts)
  let markedForDeletionDate = $derived(
    currentAccount?.custom_attributes?.marked_for_deletion_at 
      ? new Date(currentAccount.custom_attributes.marked_for_deletion_at)

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:13:33
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. (ts)
    currentAccount?.custom_attributes?.marked_for_deletion_at 
      ? new Date(currentAccount.custom_attributes.marked_for_deletion_at) 
      : null

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:17:21
Error: Property 'custom_attributes' does not exist on type 'UserAccount'. (ts)
  let markedForDeletionReason = $derived(
    currentAccount?.custom_attributes?.marked_for_deletion_reason || 'manual_deletion'
  );

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AccountDelete.svelte:59:2
Error: Property 'headerActions' is missing in type '{ children: () => any; title: string; description: string; withBorder: true; }' but required in type '$$ComponentProps'. (ts)

<SectionLayout
  title={$_('GENERAL_SETTINGS.ACCOUNT_DELETE_SECTION.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AutoResolve.svelte:126:38
Error: Property 'all' does not exist on type 'LabelsStore'. (ts)
               <Select.Content>
                  {#each labelsStore.all as label}
                    <Select.Item value={label.title} label={label.title}>{label.title}</Select.Item>

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/components/AudioTranscription.svelte:34:2
Error: Property 'children' is missing in type '{ title: string; description: string; withBorder: true; headerActions: () => any; }' but required in type '$$ComponentProps'. (ts)

<SectionLayout
  title={$_('GENERAL_SETTINGS.FORM.AUDIO_TRANSCRIPTION.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/+page.svelte:58:37
Error: Property 'support_email' does not exist on type 'UserAccount'. Did you mean 'supportEmail'? (ts)
      domain = currentAccount.domain || '';
      supportEmail = currentAccount.support_email || '';

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/app/accounts/[accountId]/settings/account/+page.svelte:107:6
Error: Property 'headerActions' is missing in type '{ children: () => any; title: string; description: string; }' but required in type '$$ComponentProps'. (ts)
    <!-- General Settings Section -->
    <SectionLayout
      title={$_('GENERAL_SETTINGS.FORM.GENERAL_SECTION.TITLE')}

/mnt/c/projects/chatwoot/laravel-svelte-port/svelte-ui/src/routes/ui/[name]/+page.svelte:18:3
Error: `$:` is not allowed in runes mode, use `$derived` or `$effect` instead
https://svelte.dev/e/legacy_reactive_statement_invalid (svelte)

  $: componentName = $page.params.name;

====================================
svelte-check found 197 errors and 52 warnings in 74 files
