// WhatsApp Template Helper Functions
// Port of Vue templateHelper.js to TypeScript for Svelte

export const DEFAULT_LANGUAGE = 'en';
export const DEFAULT_CATEGORY = 'UTILITY';

export const COMPONENT_TYPES = {
  HEADER: 'HEADER',
  BODY: 'BODY',
  BUTTONS: 'BUTTONS',
} as const;

export const MEDIA_FORMATS = ['IMAGE', 'VIDEO', 'DOCUMENT'] as const;

export interface WhatsAppTemplate {
  id: string | number;
  name: string;
  category?: string;
  language?: string;
  namespace?: string;
  components: TemplateComponent[];
}

export interface TemplateComponent {
  type: string;
  format?: string;
  text?: string;
  buttons?: ButtonComponent[];
}

export interface ButtonComponent {
  type: string;
  text?: string;
  url?: string;
}

export interface TemplateParameters {
  body?: Record<string, string>;
  header?: {
    media_url?: string;
    media_type?: string;
    media_name?: string;
  };
  buttons?: Array<{
    type: string;
    parameter: string;
    url?: string;
    variables?: string[];
  }>;
}

export const findComponentByType = (template: WhatsAppTemplate, type: string): TemplateComponent | undefined => {
  return template.components?.find(component => component.type === type);
};

export const processVariable = (str: string): string => {
  return str.replace(/{{|}}/g, '');
};

export const allKeysRequired = (value: Record<string, any>): boolean => {
  const keys = Object.keys(value);
  return keys.every(key => value[key]);
};

export const replaceTemplateVariables = (templateText: string, processedParams: TemplateParameters): string => {
  return templateText.replace(/{{([^}]+)}}/g, (match, variable) => {
    const variableKey = processVariable(variable);
    return processedParams.body?.[variableKey] || `{{${variable}}}`;
  });
};

export const buildTemplateParameters = (template: WhatsAppTemplate, hasMediaHeaderValue: boolean): TemplateParameters => {
  const allVariables: TemplateParameters = {};

  const bodyComponent = findComponentByType(template, COMPONENT_TYPES.BODY);
  const headerComponent = findComponentByType(template, COMPONENT_TYPES.HEADER);

  if (!bodyComponent) return allVariables;

  const templateString = bodyComponent.text || '';

  // Process body variables
  const matchedVariables = templateString.match(/{{([^}]+)}}/g);
  if (matchedVariables) {
    allVariables.body = {};
    matchedVariables.forEach(variable => {
      const key = processVariable(variable);
      if (allVariables.body) {
        allVariables.body[key] = '';
      }
    });
  }

  if (hasMediaHeaderValue && headerComponent) {
    if (!allVariables.header) allVariables.header = {};
    allVariables.header.media_url = '';
    allVariables.header.media_type = headerComponent.format?.toLowerCase();

    // For document templates, include media_name field for filename support
    if (headerComponent.format?.toLowerCase() === 'document') {
      allVariables.header.media_name = '';
    }
  }

  // Process button variables
  const buttonComponents = template.components.filter(
    component => component.type === COMPONENT_TYPES.BUTTONS
  );

  buttonComponents.forEach(buttonComponent => {
    if (buttonComponent.buttons) {
      buttonComponent.buttons.forEach((button, index) => {
        // Handle URL buttons with variables
        if (button.type === 'URL' && button.url && button.url.includes('{{')) {
          const buttonVars = button.url.match(/{{([^}]+)}}/g) || [];
          if (buttonVars.length > 0) {
            if (!allVariables.buttons) allVariables.buttons = [];
            allVariables.buttons[index] = {
              type: 'url',
              parameter: '',
              url: button.url,
              variables: buttonVars.map(v => processVariable(v)),
            };
          }
        }

        // Handle copy code buttons
        if (button.type === 'COPY_CODE') {
          if (!allVariables.buttons) allVariables.buttons = [];
          allVariables.buttons[index] = {
            type: 'copy_code',
            parameter: '',
          };
        }
      });
    }
  });

  return allVariables;
};
