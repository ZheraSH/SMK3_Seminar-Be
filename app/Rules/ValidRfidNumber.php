<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidRfidNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            // Call external API to validate RFID number
            $response = Http::timeout(10)->get('https://mischool.mijurnal.com/api/rfid-check', [
                'rfid' => $value
            ]);

            // Check if API call was successful and RFID is valid
            if (!$response->successful()) {
                Log::error('RFID validation API error', [
                    'rfid' => $value,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                $fail('Tidak dapat memvalidasi nomor RFID. Silakan coba lagi.');
                return;
            }

            $data = $response->json();

            // Check if the RFID is registered in the external system
            // Adjust this logic based on the actual API response structure
            // Assuming the API returns success: true if RFID is valid
            if (!isset($data['success']) || $data['success'] !== true) {
                $fail('Nomor RFID tidak terdaftar di sistem atau tidak valid.');
                return;
            }
        } catch (\Exception $e) {
            Log::error('RFID validation exception', [
                'rfid' => $value,
                'error' => $e->getMessage()
            ]);
            $fail('Terjadi kesalahan saat memvalidasi nomor RFID. Silakan coba lagi.');
        }
    }
}
