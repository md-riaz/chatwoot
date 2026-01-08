<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Http\Controllers\Controller;
use App\Models\ContactInbox;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    protected ?ContactInbox $contactInbox = null;

    /**
     * Get the contact inbox from the request token.
     */
    protected function resolveContactInbox(Request $request): ?ContactInbox
    {
        if ($this->contactInbox) {
            return $this->contactInbox;
        }

        $token = $request->header('X-Auth-Token');

        if (empty($token)) {
            return null;
        }

        $this->contactInbox = ContactInbox::where('source_id', $token)->first();

        return $this->contactInbox;
    }

    /**
     * Get the inbox from the request.
     */
    protected function getInboxFromToken(Request $request)
    {
        $contactInbox = $this->resolveContactInbox($request);

        return $contactInbox?->inbox;
    }

    /**
     * Get the contact from the request.
     */
    protected function getContactFromToken(Request $request)
    {
        $contactInbox = $this->resolveContactInbox($request);

        return $contactInbox?->contact;
    }

    /**
     * Check if the widget is authenticated.
     */
    protected function isAuthenticated(Request $request): bool
    {
        return $this->resolveContactInbox($request) !== null;
    }
}
