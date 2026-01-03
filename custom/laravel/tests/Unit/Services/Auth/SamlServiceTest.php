<?php

namespace Tests\Unit\Services\Auth;

use App\Models\Account;
use App\Models\AccountSamlSetting;
use App\Models\User;
use App\Services\Auth\SamlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlServiceTest extends TestCase
{
    use RefreshDatabase;

    private SamlService $samlService;
    private Account $account;
    private AccountSamlSetting $samlSettings;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->samlService = new SamlService();
        
        $this->account = Account::factory()->create();
        $this->samlSettings = AccountSamlSetting::factory()->create([
            'account_id' => $this->account->id,
            'sso_url' => 'https://idp.example.com/saml/sso',
            'idp_entity_id' => 'https://idp.example.com/saml/metadata',
            'certificate' => $this->generateTestCertificate(),
        ]);
    }

    public function test_generate_metadata_returns_correct_structure()
    {
        $metadata = $this->samlService->generateMetadata($this->account);

        $this->assertArrayHasKey('sp', $metadata);
        $this->assertArrayHasKey('idp', $metadata);
        
        $this->assertEquals($this->samlSettings->idp_entity_id, $metadata['idp']['entityId']);
        $this->assertEquals($this->samlSettings->sso_url, $metadata['idp']['singleSignOnService']['url']);
        
        $this->assertStringContains('/saml/sp/' . $this->account->id, $metadata['sp']['entityId']);
        $this->assertStringContains('/saml/acs/' . $this->account->id, $metadata['sp']['assertionConsumerService']['url']);
    }

    public function test_generate_metadata_throws_exception_when_no_saml_settings()
    {
        $accountWithoutSaml = Account::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SAML settings not found for account');
        
        $this->samlService->generateMetadata($accountWithoutSaml);
    }

    public function test_initiate_authentication_returns_redirect_url()
    {
        $redirectUrl = $this->samlService->initiateAuthentication($this->account);
        
        $this->assertStringStartsWith($this->samlSettings->sso_url, $redirectUrl);
        $this->assertStringContains('SAMLRequest=', $redirectUrl);
    }

    public function test_initiate_authentication_includes_relay_state()
    {
        $relayState = '/app/dashboard';
        $redirectUrl = $this->samlService->initiateAuthentication($this->account, $relayState);
        
        $this->assertStringContains('RelayState=' . urlencode($relayState), $redirectUrl);
    }

    public function test_initiate_authentication_throws_exception_when_saml_not_enabled()
    {
        $this->samlSettings->update(['sso_url' => null]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SAML not enabled for this account');
        
        $this->samlService->initiateAuthentication($this->account);
    }

    public function test_validate_certificate_returns_true_for_valid_certificate()
    {
        $validCert = $this->generateTestCertificate();
        
        $result = $this->samlService->validateCertificate($validCert);
        
        $this->assertTrue($result);
    }

    public function test_validate_certificate_returns_false_for_invalid_certificate()
    {
        $invalidCert = 'invalid-certificate-data';
        
        $result = $this->samlService->validateCertificate($invalidCert);
        
        $this->assertFalse($result);
    }

    public function test_generate_sp_certificate_returns_certificate_and_key()
    {
        $result = $this->samlService->generateSpCertificate();
        
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('private_key', $result);
        
        $this->assertStringContains('-----BEGIN CERTIFICATE-----', $result['certificate']);
        $this->assertStringContains('-----BEGIN PRIVATE KEY-----', $result['private_key']);
    }

    public function test_process_response_creates_user_from_saml_assertion()
    {
        $samlResponse = $this->generateTestSamlResponse();
        
        $user = $this->samlService->processResponse($this->account, $samlResponse);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('saml', $user->provider);
    }

    private function generateTestCertificate(): string
    {
        // Generate a test certificate for testing purposes
        $privateKey = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $dn = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'Test State',
            'localityName' => 'Test City',
            'organizationName' => 'Test Organization',
            'organizationalUnitName' => 'Test Unit',
            'commonName' => 'Test Certificate',
            'emailAddress' => 'test@example.com',
        ];

        $csr = openssl_csr_new($dn, $privateKey, ['digest_alg' => 'sha256']);
        $cert = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);

        openssl_x509_export($cert, $certOut);
        
        return $certOut;
    }

    private function generateTestSamlResponse(): string
    {
        // Generate a minimal test SAML response
        $samlResponse = '<?xml version="1.0" encoding="UTF-8"?>
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="_test_response_id"
                Version="2.0"
                IssueInstant="' . now()->toISOString() . '"
                InResponseTo="_test_request_id">
    <saml:Issuer>' . $this->samlSettings->idp_entity_id . '</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
    <saml:Assertion xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_test_assertion_id"
                    Version="2.0"
                    IssueInstant="' . now()->toISOString() . '">
        <saml:Issuer>' . $this->samlSettings->idp_entity_id . '</saml:Issuer>
        <saml:Subject>
            <saml:NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress">test@example.com</saml:NameID>
        </saml:Subject>
        <saml:AttributeStatement>
            <saml:Attribute Name="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname">
                <saml:AttributeValue>Test</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname">
                <saml:AttributeValue>User</saml:AttributeValue>
            </saml:Attribute>
        </saml:AttributeStatement>
    </saml:Assertion>
</samlp:Response>';

        return base64_encode($samlResponse);
    }
}