<script lang="ts">
  /**
   * WhatsAppTemplateParser Component
   * Handles parsing and sending WhatsApp message templates.
   * Displays template with variable placeholders and generates input fields.
   */
  import { Input } from '$lib/components/ui/input';
  import { Button } from '$lib/components/ui/button';
  import { Label } from '$lib/components/ui/label';
  import type { Snippet } from 'svelte';
  import {
    buildTemplateParameters,
    replaceTemplateVariables,
    DEFAULT_LANGUAGE,
    DEFAULT_CATEGORY,
    COMPONENT_TYPES,
    MEDIA_FORMATS,
    findComponentByType,
    type WhatsAppTemplate,
    type TemplateParameters,
  } from '$lib/helpers/templateHelper';

  interface Props {
    template: WhatsAppTemplate;
    onsendMessage?: (payload: any) => void;
    onback?: () => void;
    children?: Snippet<[{ sendMessage: () => void; goBack: () => void; disabled: boolean }]>;
  }

  let { template, onsendMessage, onback, children }: Props = $props();

  let processedParams = $state<TemplateParameters>({});

  const headerComponent = $derived(findComponentByType(template, COMPONENT_TYPES.HEADER));
  const bodyComponent = $derived(findComponentByType(template, COMPONENT_TYPES.BODY));
  const bodyText = $derived(bodyComponent?.text || '');
  
  const hasMediaHeader = $derived(
    MEDIA_FORMATS.includes(headerComponent?.format as any)
  );
  
  const formatType = $derived(() => {
    const format = headerComponent?.format;
    return format ? format.charAt(0) + format.slice(1).toLowerCase() : '';
  });
  
  const isDocumentTemplate = $derived(
    headerComponent?.format?.toLowerCase() === 'document'
  );
  
  const hasVariables = $derived(
    bodyText?.match(/{{([^}]+)}}/g) !== null
  );
  
  const renderedTemplate = $derived(
    replaceTemplateVariables(bodyText, processedParams)
  );
  
  const isFormInvalid = $derived(() => {
    if (!hasVariables && !hasMediaHeader) return false;

    if (hasMediaHeader && !processedParams.header?.media_url) {
      return true;
    }

    if (hasVariables && processedParams.body) {
      const hasEmptyBodyVariable = Object.values(processedParams.body).some(
        value => !value
      );
      if (hasEmptyBodyVariable) return true;
    }

    if (processedParams.buttons) {
      const hasEmptyButtonParameter = processedParams.buttons.some(
        button => !button.parameter
      );
      if (hasEmptyButtonParameter) return true;
    }

    return false;
  });

  const languageLabel = $derived(
    `Language: ${template.language || DEFAULT_LANGUAGE}`
  );
  
  const categoryLabel = $derived(
    `Category: ${template.category || DEFAULT_CATEGORY}`
  );

  // Initialize template parameters
  $effect(() => {
    processedParams = buildTemplateParameters(template, hasMediaHeader);
  });

  function updateMediaUrl(value: string) {
    if (!processedParams.header) processedParams.header = {};
    processedParams.header.media_url = value;
  }

  function updateMediaName(value: string) {
    if (!processedParams.header) processedParams.header = {};
    processedParams.header.media_name = value;
  }

  function updateBodyVariable(key: string, value: string) {
    if (!processedParams.body) processedParams.body = {};
    processedParams.body[key] = value;
  }

  function updateButtonParameter(index: number, value: string) {
    if (!processedParams.buttons) return;
    if (processedParams.buttons[index]) {
      processedParams.buttons[index].parameter = value;
    }
  }

  function sendMessage() {
    if (isFormInvalid()) return;

    const { name, category, language, namespace } = template;

    const payload = {
      message: renderedTemplate,
      templateParams: {
        name,
        category,
        language,
        namespace,
        processedParams,
      },
    };

    onsendMessage?.(payload);
  }

  function goBack() {
    onback?.();
  }
</script>

<div class="flex flex-col gap-4 w-full">
  <!-- Template Info -->
  <div class="flex gap-2 text-sm text-slate-600 dark:text-slate-400">
    <span>{languageLabel}</span>
    <span>•</span>
    <span>{categoryLabel}</span>
  </div>

  <!-- Media Header -->
  {#if hasMediaHeader}
    <div class="space-y-2">
      <Label for="media-url">{formatType()} URL *</Label>
      <Input
        id="media-url"
        type="url"
        placeholder={`Enter ${formatType()?.toLowerCase()} URL`}
        value={processedParams.header?.media_url || ''}
        oninput={(e: Event & { currentTarget: HTMLInputElement }) => updateMediaUrl(e.currentTarget.value)}
        required
      />
      
      {#if isDocumentTemplate}
        <div class="mt-2">
          <Label for="media-name">Document Name</Label>
          <Input
            id="media-name"
            type="text"
            placeholder="Enter document name (optional)"
            value={processedParams.header?.media_name || ''}
            oninput={(e: Event & { currentTarget: HTMLInputElement }) => updateMediaName(e.currentTarget.value)}
          />
        </div>
      {/if}
    </div>
  {/if}

  <!-- Body Template Preview -->
  <div class="space-y-2">
    <Label>Template Message</Label>
    <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
      <p class="text-sm whitespace-pre-wrap">{renderedTemplate}</p>
    </div>
  </div>

  <!-- Body Variables -->
  {#if hasVariables && processedParams.body}
    <div class="space-y-3">
      <Label>Template Variables</Label>
      {#each Object.keys(processedParams.body) as key}
        <div class="space-y-1">
          <Label for={`var-${key}`}>{key} *</Label>
          <Input
            id={`var-${key}`}
            type="text"
            placeholder={`Enter value for {{${key}}}`}
            value={processedParams.body[key]}
            oninput={(e: Event & { currentTarget: HTMLInputElement }) => updateBodyVariable(key, e.currentTarget.value)}
            required
          />
        </div>
      {/each}
    </div>
  {/if}

  <!-- Button Parameters -->
  {#if processedParams.buttons}
    <div class="space-y-3">
      <Label>Button Parameters</Label>
      {#each processedParams.buttons as button, index}
        <div class="space-y-1">
          <Label for={`button-${index}`}>
            {button.type === 'url' ? 'URL Parameter' : 'Code'} *
          </Label>
          <Input
            id={`button-${index}`}
            type="text"
            placeholder={button.type === 'url' ? 'Enter URL parameter' : 'Enter code'}
            value={button.parameter}
            oninput={(e: Event & { currentTarget: HTMLInputElement }) => updateButtonParameter(index, e.currentTarget.value)}
            required
          />
        </div>
      {/each}
    </div>
  {/if}

  <!-- Actions Slot or Default Buttons -->
  {#if children}
    {@render children({ sendMessage, goBack, disabled: isFormInvalid() })}
  {:else}
    <div class="flex gap-3 justify-between items-end w-full">
      <Button variant="outline" class="w-full" onclick={goBack}>
        Back
      </Button>
      <Button class="w-full" disabled={isFormInvalid()} onclick={sendMessage}>
        Send Message
      </Button>
    </div>
  {/if}
</div>
