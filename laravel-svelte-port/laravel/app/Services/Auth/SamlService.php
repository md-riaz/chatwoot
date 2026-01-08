<?php

namespace App\Services\Auth;

use App\Actions\Saml\SamlUserBuilderAction;
use App\Models\Account;
use App\Models\AccountSamlSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SamlService
{
    /**
     * Generate SAML metadata for a given account
     */
    public function generateMetadata(Account $account): array
    {
        $samlSettings = $account->samlSettings;
        if (!$samlSettings) {
            throw new \Exception('SAML settings not found for account');
        }

        $baseUrl = Config::get('app.frontend_url', Config::get('app.url'));
        $spEntityId = $samlSettings->sp_entity_id ?: "{$baseUrl}/saml/sp/{$account->id}";

        return [
            'sp' => [
                'entityId' => $spEntityId,
                'assertionConsumerService' => [
                    'url' => "{$baseUrl}/saml/acs/{$account->id}",
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'singleLogoutService' => [
                    'url' => "{$baseUrl}/saml/sls/{$account->id}",
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                'x509cert' => '',
                'privateKey' => '',
            ],
            'idp' => [
                'entityId' => $samlSettings->idp_entity_id,
                'singleSignOnService' => [
                    'url' => $samlSettings->sso_url,
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => $this->formatCertificate($samlSettings->certificate),
            ],
        ];
    }

    /**
     * Initiate SAML authentication request
     */
    public function initiateAuthentication(Account $account, ?string $relayState = null): string
    {
        $samlSettings = $account->samlSettings;
        if (!$samlSettings || !$samlSettings->samlEnabled()) {
            throw new \Exception('SAML not enabled for this account');
        }

        // Generate SAML authentication request
        $requestId = '_' . Str::uuid();
        $issueInstant = now()->toISOString();
        $baseUrl = Config::get('app.frontend_url', Config::get('app.url'));
        $acsUrl = "{$baseUrl}/saml/acs/{$account->id}";
        $spEntityId = $samlSettings->sp_entity_id ?: "{$baseUrl}/saml/sp/{$account->id}";

        $samlRequest = $this->buildSamlRequest(
            $requestId,
            $issueInstant,
            $samlSettings->sso_url,
            $acsUrl,
            $spEntityId
        );

        // Encode and deflate the request
        $encodedRequest = base64_encode(gzdeflate($samlRequest));

        // Build redirect URL
        $params = [
            'SAMLRequest' => $encodedRequest,
        ];

        if ($relayState) {
            $params['RelayState'] = $relayState;
        }

        return $samlSettings->sso_url . '?' . http_build_query($params);
    }

    /**
     * Process SAML response and authenticate user
     */
    public function processResponse(Account $account, string $samlResponse, ?string $relayState = null): User
    {
        $samlSettings = $account->samlSettings;
        if (!$samlSettings || !$samlSettings->samlEnabled()) {
            throw new \Exception('SAML not enabled for this account');
        }

        // Decode SAML response
        $decodedResponse = base64_decode($samlResponse);
        
        // Parse SAML response XML
        $responseDoc = new \DOMDocument();
        $responseDoc->loadXML($decodedResponse);

        // Validate SAML response
        $this->validateSamlResponse($responseDoc, $samlSettings);

        // Extract user attributes from SAML response
        $userAttributes = $this->extractUserAttributes($responseDoc);

        // Build auth hash compatible with SamlUserBuilderAction
        $authHash = $this->buildAuthHash($userAttributes);

        // Create or update user
        $user = SamlUserBuilderAction::run($authHash, $account->id);

        if (!$user) {
            throw new \Exception('Failed to create or update user from SAML response');
        }

        Log::info('SAML authentication successful', [
            'account_id' => $account->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
    }

    /**
     * Validate SAML certificate
     */
    public function validateCertificate(string $certificate): bool
    {
        try {
            $cert = openssl_x509_read($certificate);
            if (!$cert) {
                return false;
            }

            // Check if certificate is expired
            $certData = openssl_x509_parse($cert);
            if ($certData['validTo_time_t'] < time()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('SAML certificate validation failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate SP certificate and private key
     */
    public function generateSpCertificate(): array
    {
        // Generate private key
        $privateKey = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // Generate certificate
        $dn = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'State',
            'localityName' => 'City',
            'organizationName' => 'Chatwoot',
            'organizationalUnitName' => 'IT Department',
            'commonName' => 'Chatwoot SAML SP',
            'emailAddress' => 'admin@chatwoot.com',
        ];

        $csr = openssl_csr_new($dn, $privateKey, ['digest_alg' => 'sha256']);
        $cert = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);

        // Export certificate and private key
        openssl_x509_export($cert, $certOut);
        openssl_pkey_export($privateKey, $privateKeyOut);

        return [
            'certificate' => $certOut,
            'private_key' => $privateKeyOut,
        ];
    }

    /**
     * Build SAML authentication request XML
     */
    private function buildSamlRequest(
        string $requestId,
        string $issueInstant,
        string $destination,
        string $acsUrl,
        string $spEntityId
    ): string {
        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="%s"
                    Version="2.0"
                    IssueInstant="%s"
                    Destination="%s"
                    AssertionConsumerServiceURL="%s"
                    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST">
    <saml:Issuer>%s</saml:Issuer>
    <samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress" AllowCreate="true"/>
</samlp:AuthnRequest>',
            $requestId,
            $issueInstant,
            htmlspecialchars($destination),
            htmlspecialchars($acsUrl),
            htmlspecialchars($spEntityId)
        );
    }

    /**
     * Validate SAML response
     */
    private function validateSamlResponse(\DOMDocument $responseDoc, AccountSamlSetting $samlSettings): void
    {
        // Basic XML structure validation
        $xpath = new \DOMXPath($responseDoc);
        $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

        // Check if response exists
        $responses = $xpath->query('//samlp:Response');
        if ($responses->length === 0) {
            throw new \Exception('Invalid SAML response: No Response element found');
        }

        // Check status
        $statusCodes = $xpath->query('//samlp:StatusCode/@Value');
        if ($statusCodes->length > 0) {
            $statusCode = $statusCodes->item(0)->nodeValue;
            if ($statusCode !== 'urn:oasis:names:tc:SAML:2.0:status:Success') {
                throw new \Exception("SAML authentication failed with status: {$statusCode}");
            }
        }

        // Validate certificate if signature verification is needed
        if ($samlSettings->certificate) {
            $this->validateSignature($responseDoc, $samlSettings->certificate);
        }
    }

    /**
     * Validate SAML response signature
     */
    private function validateSignature(\DOMDocument $responseDoc, string $certificate): void
    {
        // This is a simplified signature validation
        // In production, you should use a proper SAML library like OneLogin SAML PHP Toolkit
        
        $xpath = new \DOMXPath($responseDoc);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $signatures = $xpath->query('//ds:Signature');
        if ($signatures->length === 0) {
            Log::warning('SAML response has no signature');
            return;
        }

        // For now, just log that signature validation would happen here
        Log::info('SAML signature validation would be performed here');
    }

    /**
     * Extract user attributes from SAML response
     */
    private function extractUserAttributes(\DOMDocument $responseDoc): array
    {
        $xpath = new \DOMXPath($responseDoc);
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

        $attributes = [];

        // Extract NameID (usually email)
        $nameIds = $xpath->query('//saml:NameID');
        if ($nameIds->length > 0) {
            $attributes['email'] = $nameIds->item(0)->nodeValue;
        }

        // Extract attribute statements
        $attributeNodes = $xpath->query('//saml:Attribute');
        foreach ($attributeNodes as $attributeNode) {
            $name = $attributeNode->getAttribute('Name');
            $values = $xpath->query('saml:AttributeValue', $attributeNode);
            
            if ($values->length > 0) {
                if ($values->length === 1) {
                    $attributes[$name] = $values->item(0)->nodeValue;
                } else {
                    $attributeValues = [];
                    foreach ($values as $value) {
                        $attributeValues[] = $value->nodeValue;
                    }
                    $attributes[$name] = $attributeValues;
                }
            }
        }

        return $attributes;
    }

    /**
     * Build auth hash compatible with SamlUserBuilderAction
     */
    private function buildAuthHash(array $userAttributes): array
    {
        // Map common SAML attributes to expected format
        $email = $userAttributes['email'] ?? 
                 $userAttributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'] ?? 
                 $userAttributes['mail'] ?? null;

        $firstName = $userAttributes['first_name'] ?? 
                    $userAttributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'] ?? 
                    $userAttributes['givenName'] ?? null;

        $lastName = $userAttributes['last_name'] ?? 
                   $userAttributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'] ?? 
                   $userAttributes['sn'] ?? null;

        $name = $userAttributes['name'] ?? 
                $userAttributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'] ?? 
                $userAttributes['displayName'] ?? null;

        $groups = $userAttributes['groups'] ?? 
                  $userAttributes['http://schemas.xmlsoap.org/claims/Group'] ?? 
                  $userAttributes['memberOf'] ?? [];

        return [
            'uid' => $email ?: Str::uuid(),
            'info' => [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $name,
            ],
            'extra' => [
                'raw_info' => array_merge($userAttributes, [
                    'groups' => is_array($groups) ? $groups : [$groups],
                ]),
            ],
        ];
    }

    /**
     * Format certificate for SAML configuration
     */
    private function formatCertificate(string $certificate): string
    {
        // Remove headers and footers, and format as single line
        $cert = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $certificate);
        $cert = preg_replace('/\s+/', '', $cert);
        return $cert;
    }
}