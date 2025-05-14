<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * WhatsApp API endpoint
     * 
     * @var string
     */
    protected $apiUrl;
    
    /**
     * WhatsApp API token
     * 
     * @var string
     */
    protected $apiToken;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', 'https://api.whatsapp.com/v1/messages');
        $this->apiToken = config('services.whatsapp.api_token');
    }
    
    /**
     * Send a message with a PDF attachment to a WhatsApp number
     *
     * @param string $phoneNumber The phone number to send the message to (with country code)
     * @param string $message The message to send
     * @param string $pdfPath The path to the PDF file
     * @return bool Whether the message was sent successfully
     */
    public function sendPdf(string $phoneNumber, string $message, string $pdfPath): bool
    {
        try {
            // Validate phone number format (should start with country code)
            if (!preg_match('/^\+[0-9]{10,15}$/', $phoneNumber)) {
                Log::error("Invalid phone number format: $phoneNumber");
                return false;
            }
            
            // Check if PDF file exists
            if (!file_exists($pdfPath)) {
                Log::error("PDF file not found: $pdfPath");
                return false;
            }
            
            // Get the file contents and encode as base64
            $pdfContent = base64_encode(file_get_contents($pdfPath));
            $filename = basename($pdfPath);
            
            // Prepare the request payload
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $phoneNumber,
                'type' => 'document',
                'document' => [
                    'filename' => $filename,
                    'caption' => $message,
                    'document' => $pdfContent
                ]
            ];
            
            // Make the API request
            $response = Http::withToken($this->apiToken)
                ->post($this->apiUrl, $payload);
            
            // Check if the request was successful
            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to $phoneNumber");
                return true;
            } else {
                Log::error("Failed to send WhatsApp message: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp message: " . $e->getMessage());
            return false;
        }
    }
}
