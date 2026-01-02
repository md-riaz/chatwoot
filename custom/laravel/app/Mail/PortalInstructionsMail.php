<?php

namespace App\Mail;

use App\Mail\ApplicationMailable;
use App\Models\Account;
use App\Models\Portal;
use Illuminate\Mail\Mailables\Envelope;

class PortalInstructionsMail extends ApplicationMailable
{
    protected Portal $portal;
    protected string $instructionType;
    protected array $dnsRecords = [];
    protected ?string $customTo = null;

    public function __construct(
        Portal $portal,
        string $instructionType = 'cname_instructions',
        array $dnsRecords = [],
        ?string $customTo = null
    ) {
        parent::__construct($portal->account);
        
        $this->portal = $portal;
        $this->instructionType = $instructionType;
        $this->dnsRecords = $dnsRecords;
        $this->customTo = $customTo;
        
        $this->actionUrl = $this->generateActionUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $to = $this->customTo ?? $this->getDefaultRecipient();
        
        return new Envelope(
            from: $this->getFromAddress(),
            to: [$to],
            subject: $this->getSubject(),
        );
    }

    /**
     * Create CNAME setup instructions email.
     */
    public static function sendCnameInstructions(
        Portal $portal,
        array $dnsRecords,
        string $to
    ): static {
        return new static($portal, 'cname_instructions', $dnsRecords, $to);
    }

    /**
     * Create custom domain configuration email.
     */
    public static function customDomainConfiguration(
        Portal $portal,
        array $dnsRecords,
        string $to
    ): static {
        return new static($portal, 'custom_domain_configuration', $dnsRecords, $to);
    }

    /**
     * Create DNS record generation email.
     */
    public static function dnsRecordGeneration(
        Portal $portal,
        array $dnsRecords,
        string $to
    ): static {
        return new static($portal, 'dns_record_generation', $dnsRecords, $to);
    }

    /**
     * Create portal configuration guidance email.
     */
    public static function portalConfigurationGuidance(
        Portal $portal,
        string $to
    ): static {
        return new static($portal, 'portal_configuration_guidance', [], $to);
    }

    /**
     * Get email subject based on instruction type.
     */
    protected function getSubject(): string
    {
        $portalName = $this->portal->name;

        return match ($this->instructionType) {
            'cname_instructions' => "CNAME Setup Instructions for {$portalName}",
            'custom_domain_configuration' => "Custom Domain Configuration for {$portalName}",
            'dns_record_generation' => "DNS Records for {$portalName}",
            'portal_configuration_guidance' => "Portal Configuration Guide for {$portalName}",
            default => "Portal Setup Instructions for {$portalName}",
        };
    }

    /**
     * Get view name for the email template.
     */
    protected function getViewName(): string
    {
        return "emails.portal.{$this->instructionType}";
    }

    /**
     * Get view data for the email template.
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'portal' => $this->portal,
            'instruction_type' => $this->instructionType,
            'dns_records' => $this->dnsRecords,
            'cname_record' => $this->generateCnameRecord(),
            'verification_steps' => $this->getVerificationSteps(),
        ]);
    }

    /**
     * Generate action URL for portal settings.
     */
    protected function generateActionUrl(): string
    {
        $baseUrl = config('app.frontend_url', config('app.url'));
        return "{$baseUrl}/app/accounts/{$this->portal->account_id}/portals/{$this->portal->slug}/settings";
    }

    /**
     * Get default recipient email.
     */
    protected function getDefaultRecipient(): string
    {
        // Get account owner or first admin
        if ($this->account) {
            $owner = $this->account->users()
                ->wherePivot('role', 'administrator')
                ->first();
            
            if ($owner) {
                return $owner->email;
            }
        }

        return config('mail.from.address');
    }

    /**
     * Generate CNAME record for the portal.
     */
    protected function generateCnameRecord(): array
    {
        $portalDomain = $this->portal->custom_domain ?? $this->portal->slug . '.' . strtolower(config('app.name', 'chatwoot')) . '.com';
        $targetDomain = config('app.portal_domain', 'portals.' . strtolower(config('app.name', 'chatwoot')) . '.com');

        return [
            'type' => 'CNAME',
            'name' => $portalDomain,
            'value' => $targetDomain,
            'ttl' => 300,
        ];
    }

    /**
     * Get verification steps for DNS setup.
     */
    protected function getVerificationSteps(): array
    {
        return [
            [
                'step' => 1,
                'title' => 'Add DNS Records',
                'description' => 'Add the provided DNS records to your domain registrar or DNS provider.',
            ],
            [
                'step' => 2,
                'title' => 'Wait for Propagation',
                'description' => 'DNS changes can take up to 24-48 hours to propagate globally.',
            ],
            [
                'step' => 3,
                'title' => 'Verify Configuration',
                'description' => 'Use online DNS lookup tools to verify your records are active.',
            ],
            [
                'step' => 4,
                'title' => 'Test Portal Access',
                'description' => 'Visit your custom domain to confirm the portal is accessible.',
            ],
            [
                'step' => 5,
                'title' => 'Enable SSL',
                'description' => 'Once DNS is verified, SSL certificates will be automatically provisioned.',
            ],
        ];
    }

    /**
     * Get liquid droppables for template variables.
     */
    protected function getLiquidDroppables(): array
    {
        return array_merge(parent::getLiquidDroppables(), [
            'portal' => $this->portal,
        ]);
    }

    /**
     * Get liquid locals for template variables.
     */
    protected function getLiquidLocals(): array
    {
        return array_merge(parent::getLiquidLocals(), [
            'dns_records' => $this->dnsRecords,
            'cname_record' => $this->generateCnameRecord(),
            'verification_steps' => $this->getVerificationSteps(),
        ]);
    }
}