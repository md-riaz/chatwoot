<?php

namespace App\Services\Contact;

use App\Models\Contact;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactImportService
{
    public function processImport(int $accountId, string $filePath): array
    {
        $stream = Storage::readStream($filePath);
        if (!$stream) {
            throw new \Exception('File not readable');
        }

        $header = null;
        $processed = 0;
        $created = 0;
        $errors = [];

        while (($row = fgetcsv($stream)) !== false) {
            if (!$header) {
                $header = $row;
                continue;
            }

            $data = array_combine($header, $row);
            if (!$data) {
                $errors[] = "Invalid row format at line " . ($processed + 2);
                $processed++;
                continue;
            }

            $contactData = $this->prepareContactData($data, $accountId);
            
            if ($this->validateContactData($contactData)) {
                try {
                    Contact::create($contactData);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create contact: " . $e->getMessage();
                }
            } else {
                $errors[] = "Invalid contact data at line " . ($processed + 2);
            }

            $processed++;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'errors' => $errors,
        ];
    }

    private function prepareContactData(array $data, int $accountId): array
    {
        return [
            'account_id' => $accountId,
            'name' => trim($data['name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'phone_number' => $this->formatPhoneNumber(trim($data['phone_number'] ?? '')),
            'identifier' => trim($data['identifier'] ?? ''),
        ];
    }

    private function validateContactData(array $contactData): bool
    {
        $validator = Validator::make($contactData, [
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return false;
        }

        // At least email or phone_number must be present
        return !empty($contactData['email']) || !empty($contactData['phone_number']);
    }

    private function formatPhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
        return $phoneNumber && !str_starts_with($phoneNumber, '+') ? '+' . $phoneNumber : $phoneNumber;
    }
}