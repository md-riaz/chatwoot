<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Account;
use App\Models\AccountSamlSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SamlControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private AccountSamlSetting $samlSettings;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->account = Account::factory()->create();
        $this->samlSettings = AccountSamlSetting::factory()->create([
            'account_id' => $this->account->id,
            'sso_url' => 'https://idp.example.com/saml/sso',
            'idp_entity_id' => 'https://idp.example.com/saml/metadata',
            'certificate' => $this->generateTestCertificate(),
        ]);
    }

    public function test_config_returns_saml_configuration()
    {
        $response = $this->getJson("/saml/config/{$this->account->id}");

        $response->assertOk()
            ->assertJson([
                'saml_enabled' => true,
                'account_id' => $this->account->id,
            ])
            ->assertJsonStructure([
                'saml_enabled',
                'sso_url',
                'sp_entity_id',
                'account_id',
            ]);
    }

    public function test_config_returns_404_when_saml_not_configured()
    {
        $accountWithoutSaml = Account::factory()->create();
        
        $response = $this->getJson("/saml/config/{$accountWithoutSaml->id}");

        $response->assertNotFound()
            ->assertJson([
                'error' => 'SAML not configured for this account'
            ]);
    }

    public function test_metadata_returns_xml_metadata()
    {
        $response = $this->get("/saml/metadata/{$this->account->id}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        
        $this->assertStringContains('EntityDescriptor', $response->getContent());
        $this->assertStringContains('SPSSODescriptor', $response->getContent());
    }

    public function test_metadata_returns_404_when_saml_not_configured()
    {
        $accountWithoutSaml = Account::factory()->create();
        
        $response = $this->get("/saml/metadata/{$accountWithoutSaml->id}");

        $response->assertNotFound();
    }

    public function test_login_redirects_to_identity_provider()
    {
        $response = $this->get("/saml/login/{$this->account->id}");

        $response->assertRedirect();
        
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith($this->samlSettings->sso_url, $location);
        $this->assertStringContains('SAMLRequest=', $location);
    }

    public function test_login_includes_relay_state_in_redirect()
    {
        $relayState = '/app/dashboard';
        
        $response = $this->get("/saml/login/{$this->account->id}?RelayState=" . urlencode($relayState));

        $response->assertRedirect();
        
        $location = $response->headers->get('Location');
        $this->assertStringContains('RelayState=' . urlencode($relayState), $location);
    }

    public function test_login_stores_account_id_in_session()
    {
        $this->get("/saml/login/{$this->account->id}");

        $this->assertEquals($this->account->id, Session::get('saml_account_id'));
    }

    public function test_acs_processes_saml_response_and_authenticates_user()
    {
        $samlResponse = $this->generateTestSamlResponse();
        
        $response = $this->post("/saml/acs/{$this->account->id}", [
            'SAMLResponse' => $samlResponse,
        ]);

        $response->assertRedirect();
        
        $location = $response->headers->get('Location');
        $this->assertStringContains('saml_success=1', $location);
        
        // Check that user was created
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('saml', $user->provider);
        
        // Check that auth token was stored in session
        $this->assertNotNull(Session::get('auth_token'));
        $this->assertEquals($user->id, Session::get('user_id'));
    }

    public function test_acs_redirects_to_relay_state_when_provided()
    {
        $samlResponse = $this->generateTestSamlResponse();
        $relayState = '/app/custom-dashboard';
        
        $response = $this->post("/saml/acs/{$this->account->id}", [
            'SAMLResponse' => $samlResponse,
            'RelayState' => $relayState,
        ]);

        $response->assertRedirect();
        
        $location = $response->headers->get('Location');
        $this->assertStringContains($relayState, $location);
    }

    public function test_acs_handles_missing_saml_response()
    {
        $response = $this->post("/saml/acs/{$this->account->id}", []);

        $response->assertRedirect();
        
        $location = $response->headers->get('Location');
        $this->assertStringContains('error=saml_auth_failed', $location);
    }

    public function test_sls_logs_out_user_and_redirects()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get("/saml/sls/{$this->account->id}");

        $response->assertRedirect();
        
        $location = $response->headers->get('Location');
        $this->assertStringContains('saml_logout=1', $location);
        
        $this->assertGuest();
    }

    public function test_token_returns_authentication_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        
        Session::put('auth_token', $token);
        Session::put('user_id', $user->id);
        
        $response = $this->getJson('/auth/saml/token');

        $response->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'email',
                    'name',
                    'display_name',
                    'provider',
                ],
            ]);
        
        // Check that session data was cleared
        $this->assertNull(Session::get('auth_token'));
        $this->assertNull(Session::get('user_id'));
    }

    public function test_token_returns_401_when_no_token_in_session()
    {
        $response = $this->getJson('/auth/saml/token');

        $response->assertUnauthorized()
            ->assertJson([
                'error' => 'No authentication token available'
            ]);
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