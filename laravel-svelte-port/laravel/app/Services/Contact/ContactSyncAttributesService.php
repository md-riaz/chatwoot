<?php

namespace App\Services\Contact;

use App\Models\Contact;

/**
 * Contact Sync Attributes Service
 * 
 * Matches Rails Contacts::SyncAttributes functionality.
 * Syncs data from additional_attributes to direct fields and sets contact type.
 */
class ContactSyncAttributesService
{
    public function __construct(
        private Contact $contact
    ) {}

    public function perform(): void
    {
        $this->updateContactLocationAndCountryCode();
        $this->setContactType();
    }

    /**
     * Sync location and country_code from additional_attributes.
     * 
     * Rails pattern: additional_attributes['city'] → location field
     * Rails pattern: additional_attributes['country'] → country_code field
     */
    private function updateContactLocationAndCountryCode(): void
    {
        $additionalAttributes = $this->contact->additional_attributes ?? [];

        // Sync city from additional_attributes to location field (Rails pattern)
        if (isset($additionalAttributes['city'])) {
            $this->contact->location = $additionalAttributes['city'];
        }

        // Sync country from additional_attributes to country_code field (Rails pattern)
        if (isset($additionalAttributes['country_code'])) {
            $this->contact->country_code = $additionalAttributes['country_code'];
        } elseif (isset($additionalAttributes['country'])) {
            // Support both 'country' and 'country_code' keys for flexibility
            $this->contact->country_code = $additionalAttributes['country'];
        }
    }

    /**
     * Set contact type based on available information.
     * 
     * Rails logic:
     * - If already lead or customer, don't change
     * - If visitor and has email/phone/social details, promote to lead
     */
    private function setContactType(): void
    {
        // If already a lead or customer, don't change
        if ($this->contact->contact_type !== 0) { // 0 = visitor
            return;
        }

        // If has email, phone, or social details, promote to lead
        if ($this->hasIdentifyingInformation()) {
            $this->contact->contact_type = 1; // 1 = lead
        }
    }

    /**
     * Check if contact has identifying information (email, phone, or social details).
     */
    private function hasIdentifyingInformation(): bool
    {
        // Has email or phone number
        if (!empty($this->contact->email) || !empty($this->contact->phone_number)) {
            return true;
        }

        // Has social details (any key starting with 'social_')
        $additionalAttributes = $this->contact->additional_attributes ?? [];
        foreach ($additionalAttributes as $key => $value) {
            if (str_starts_with($key, 'social_') && !empty($value)) {
                return true;
            }
        }

        return false;
    }
}