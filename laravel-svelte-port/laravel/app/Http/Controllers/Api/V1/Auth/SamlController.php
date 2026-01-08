<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Auth\SamlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SamlController extends Controller
{
    public function __construct(
        private SamlService $samlService
    ) {}

    /**
     * Get SAML configuration for an account
     */
    public function config(Account $account): JsonResponse
    {
        $samlSettings = $account->samlSettings;
        
        if (!$samlSettings || !$samlSettings->samlEnabled()) {
            return response()->json([
                'error' => 'SAML not configured for this account'
            ], 404);
        }

        return response()->json([
            'saml_enabled' => true,
            'sso_url' => route('saml.login', ['account' => $account->id]),
            'sp_entity_id' => $samlSettings->sp_entity_id,
            'account_id' => $account->id,
        ]);
    }

    /**
     * Generate SAML metadata for an account
     */
    public function metadata(Account $account): \Illuminate\Http\Response
    {
        try {
            $metadata = $this->samlService->generateMetadata($account);
            
            $xml = $this->buildMetadataXml($metadata);
            
            return response($xml, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'inline; filename="metadata.xml"',
            ]);
        } catch (\Exception $e) {
            Log::error('SAML metadata generation failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            
            return response('SAML metadata not available', 404);
        }
    }

    /**
     * Initiate SAML authentication
     */
    public function login(Account $account, Request $request): RedirectResponse
    {
        try {
            $relayState = $request->get('RelayState');
            
            // Store account ID in session for later use
            Session::put('saml_account_id', $account->id);
            
            $redirectUrl = $this->samlService->initiateAuthentication($account, $relayState);
            
            Log::info('SAML authentication initiated', [
                'account_id' => $account->id,
                'relay_state' => $relayState,
            ]);
            
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('SAML authentication initiation failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect('/app/login?error=saml_init_failed');
        }
    }

    /**
     * Handle SAML assertion consumer service (ACS)
     */
    public function acs(Account $account, Request $request): RedirectResponse
    {
        try {
            $samlResponse = $request->input('SAMLResponse');
            $relayState = $request->input('RelayState');
            
            if (!$samlResponse) {
                throw new \Exception('No SAML response received');
            }
            
            // Process SAML response and authenticate user
            $user = $this->samlService->processResponse($account, $samlResponse, $relayState);
            
            // Log the user in
            Auth::login($user);
            
            // Generate API token for the user
            $token = $user->createToken('saml-auth')->plainTextToken;
            
            // Clear SAML session data
            Session::forget('saml_account_id');
            
            Log::info('SAML authentication completed', [
                'account_id' => $account->id,
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            
            // Redirect to the application with token
            $redirectUrl = '/app/dashboard';
            if ($relayState) {
                $redirectUrl = $relayState;
            }
            
            // For SPA, we need to pass the token somehow
            // Option 1: Redirect to a token exchange endpoint
            // Option 2: Use a temporary token that can be exchanged
            // For now, redirect to login success page with token in session
            Session::put('auth_token', $token);
            Session::put('user_id', $user->id);
            
            return redirect($redirectUrl . '?saml_success=1');
            
        } catch (\Exception $e) {
            Log::error('SAML ACS processing failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect('/app/login?error=saml_auth_failed&message=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle SAML single logout service (SLS)
     */
    public function sls(Account $account, Request $request): RedirectResponse
    {
        try {
            // Log out the current user
            if (Auth::check()) {
                $user = Auth::user();
                
                // Revoke all tokens for this user
                $user->tokens()->delete();
                
                Auth::logout();
                
                Log::info('SAML logout completed', [
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                ]);
            }
            
            // Clear all session data
            Session::flush();
            
            return redirect('/app/login?saml_logout=1');
            
        } catch (\Exception $e) {
            Log::error('SAML SLS processing failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect('/app/login?error=saml_logout_failed');
        }
    }

    /**
     * Get SAML authentication token after successful login
     * This endpoint is called by the frontend after SAML redirect
     */
    public function token(Request $request): JsonResponse
    {
        $token = Session::get('auth_token');
        $userId = Session::get('user_id');
        
        if (!$token || !$userId) {
            return response()->json([
                'error' => 'No authentication token available'
            ], 401);
        }
        
        // Clear the session data after retrieving
        Session::forget(['auth_token', 'user_id']);
        
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'display_name' => $user->display_name,
                'provider' => $user->provider,
            ],
        ]);
    }

    /**
     * Build SAML metadata XML
     */
    private function buildMetadataXml(array $metadata): string
    {
        $sp = $metadata['sp'];
        
        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     entityID="%s">
    <md:SPSSODescriptor AuthnRequestsSigned="false" WantAssertionsSigned="false"
                        protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <md:NameIDFormat>%s</md:NameIDFormat>
        <md:AssertionConsumerService Binding="%s"
                                     Location="%s"
                                     index="1"/>
        <md:SingleLogoutService Binding="%s"
                                Location="%s"/>
    </md:SPSSODescriptor>
</md:EntityDescriptor>',
            htmlspecialchars($sp['entityId']),
            htmlspecialchars($sp['NameIDFormat']),
            htmlspecialchars($sp['assertionConsumerService']['binding']),
            htmlspecialchars($sp['assertionConsumerService']['url']),
            htmlspecialchars($sp['singleLogoutService']['binding']),
            htmlspecialchars($sp['singleLogoutService']['url'])
        );
    }
}