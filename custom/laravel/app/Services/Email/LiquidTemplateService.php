<?php

namespace App\Services\Email;

use Illuminate\Support\Facades\Log;

class LiquidTemplateService
{
    /**
     * Process liquid template with variables.
     */
    public function process(string $template, array $variables = []): string
    {
        try {
            // Simple liquid-like template processing
            // This is a basic implementation - for production, consider using a proper Liquid parser
            $processed = $template;

            // Process simple variable substitutions
            foreach ($variables as $key => $value) {
                if (is_scalar($value) || is_null($value)) {
                    $processed = str_replace("{{{{ {$key} }}}}", (string) $value, $processed);
                } elseif (is_object($value) || is_array($value)) {
                    $processed = $this->processObjectVariables($processed, $key, $value);
                }
            }

            // Process global config variables
            if (isset($variables['global_config']) && is_array($variables['global_config'])) {
                foreach ($variables['global_config'] as $configKey => $configValue) {
                    $processed = str_replace("{{{{ global_config.{$configKey} }}}}", (string) $configValue, $processed);
                }
            }

            return $processed;
        } catch (\Exception $e) {
            Log::error('Liquid template processing failed', [
                'error' => $e->getMessage(),
                'template' => substr($template, 0, 200),
            ]);
            return $template;
        }
    }

    /**
     * Process object/array variables in template.
     */
    protected function processObjectVariables(string $template, string $key, $value): string
    {
        if (is_object($value)) {
            // Handle object properties
            $reflection = new \ReflectionClass($value);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            
            foreach ($properties as $property) {
                $propertyName = $property->getName();
                $propertyValue = $property->getValue($value);
                
                if (is_scalar($propertyValue) || is_null($propertyValue)) {
                    $template = str_replace(
                        "{{{{ {$key}.{$propertyName} }}}}",
                        (string) $propertyValue,
                        $template
                    );
                }
            }

            // Handle common methods
            $commonMethods = ['getName', 'getEmail', 'getDisplayId', 'getAvailableName'];
            foreach ($commonMethods as $method) {
                if (method_exists($value, $method)) {
                    $methodValue = $value->$method();
                    $methodKey = $this->camelToSnake(str_replace('get', '', $method));
                    $template = str_replace(
                        "{{{{ {$key}.{$methodKey} }}}}",
                        (string) $methodValue,
                        $template
                    );
                }
            }
        } elseif (is_array($value)) {
            // Handle array values
            foreach ($value as $arrayKey => $arrayValue) {
                if (is_scalar($arrayValue) || is_null($arrayValue)) {
                    $template = str_replace(
                        "{{{{ {$key}.{$arrayKey} }}}}",
                        (string) $arrayValue,
                        $template
                    );
                }
            }
        }

        return $template;
    }

    /**
     * Convert camelCase to snake_case.
     */
    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Get available template variables for documentation.
     */
    public function getAvailableVariables(): array
    {
        return [
            'user' => [
                'name' => 'User full name',
                'email' => 'User email address',
                'available_name' => 'User display name',
            ],
            'account' => [
                'name' => 'Account name',
                'locale' => 'Account locale',
            ],
            'conversation' => [
                'display_id' => 'Conversation display ID',
                'status' => 'Conversation status',
            ],
            'inbox' => [
                'name' => 'Inbox name',
                'channel_type' => 'Channel type',
            ],
            'message' => [
                'content' => 'Message content',
                'created_at' => 'Message creation time',
            ],
            'global_config' => [
                'BRAND_NAME' => 'Brand name',
                'BRAND_URL' => 'Brand URL',
            ],
            'action_url' => 'Action URL for buttons/links',
            'attachment_url' => 'Attachment download URL',
        ];
    }
}