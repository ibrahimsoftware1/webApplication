<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService
{
    private string $baseUrl;
    private string $authUrl;
    private string $identifier;
    private string $secretKey;
    private ?string $accessToken = null;
    private int $tokenExpiresAt = 0;

    public function __construct()
    {
        $this->baseUrl = config('payment.fib.base_url', 'https://fib.stage.fib.iq');
        $this->authUrl = config('payment.fib.auth_url', 'https://fib.stage.fib.iq/auth/realms/fib-online-shop/protocol/openid-connect/token');
        $this->identifier = config('payment.fib.identifier');
        $this->secretKey = config('payment.fib.secret_key');
        
        // Validate configuration on construction
        if (empty($this->identifier) || empty($this->secretKey)) {
            Log::error('FIB PaymentService: Missing required configuration', [
                'identifier_set' => !empty($this->identifier),
                'secret_key_set' => !empty($this->secretKey),
                'base_url' => $this->baseUrl,
                'auth_url' => $this->authUrl,
            ]);
            throw new Exception('FIB Payment Gateway configuration is incomplete. Please check your .env file.');
        }
        
        Log::info('FIB PaymentService: Configuration loaded and validated', [
            'base_url' => $this->baseUrl,
            'auth_url' => $this->authUrl,
            'identifier' => $this->identifier,
            'secret_key_length' => strlen($this->secretKey),
            'identifier_matches' => $this->identifier === 'salahadin-testig-creds',
        ]);
    }

    /**
     * Get access token from FIB API
     */
    private function getAccessToken(): string
    {
        // Check if we have a valid token
        if ($this->accessToken && time() < $this->tokenExpiresAt) {
            Log::debug('FIB: Using cached access token', [
                'expires_at' => $this->tokenExpiresAt,
                'current_time' => time(),
            ]);
            return $this->accessToken;
        }

        try {
            $authData = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->identifier,
                'client_secret' => $this->secretKey,
            ];

            Log::info('FIB: Requesting access token', [
                'auth_url' => $this->authUrl,
                'identifier' => $this->identifier,
                'has_secret' => !empty($this->secretKey),
            ]);

            $response = Http::withoutVerifying()
                ->asForm()
                ->post($this->authUrl, $authData);

            Log::info('FIB: Auth response received', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500),
            ]);

            if (!$response->successful()) {
                Log::error('FIB Auth Failed - Detailed Error', [
                    'status' => $response->status(),
                    'status_text' => $response->status() === 400 ? 'Bad Request' : ($response->status() === 401 ? 'Unauthorized' : 'Unknown'),
                    'body' => $response->body(),
                    'json' => $response->json(),
                    'auth_url' => $this->authUrl,
                    'identifier' => $this->identifier,
                    'request_data' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->identifier,
                        'client_secret' => '***HIDDEN***',
                    ],
                ]);
                throw new Exception('Failed to authenticate with FIB payment gateway: ' . $response->body());
            }

            $data = $response->json();
            $this->accessToken = $data['access_token'] ?? null;
            $this->tokenExpiresAt = time() + ($data['expires_in'] ?? 60) - 10; // Refresh 10 seconds before expiry

            if (!$this->accessToken) {
                Log::error('FIB: Access token not found in response', [
                    'response_data' => $data,
                ]);
                throw new Exception('Access token not found in FIB response');
            }

            Log::info('FIB: Access token obtained successfully', [
                'token_preview' => substr($this->accessToken, 0, 20) . '...',
                'expires_in' => $data['expires_in'] ?? 'unknown',
                'token_expires_at' => $this->tokenExpiresAt,
            ]);

            return $this->accessToken;
        } catch (Exception $e) {
            Log::error('FIB Token Error - Full Details', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'auth_url' => $this->authUrl,
                'identifier' => $this->identifier,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a payment
     */
    public function createPayment(array $data): array
    {
        try {
            Log::info('FIB: Starting payment creation', [
                'input_data' => $data,
                'base_url' => $this->baseUrl,
            ]);

            $token = $this->getAccessToken();

            // Convert amount to fils (smallest currency unit for IQD)
            // 1 IQD = 1000 fils
            $amountInIQD = is_numeric($data['amount']) ? (float)$data['amount'] : 0;
            $amountInFils = (int)($amountInIQD * 1000);
            
            // FIB API payload format based on official documentation
            // According to FIB API docs, try multiple field name variations
            // Based on Postman documentation, the API might expect:
            // - monetaryValue or amount (in IQD or fils)
            // - currency (IQD)
            // - description
            // - statusCallbackUrl or callback_url
            // - recipientPhone or recipient_phone
            
            // FIB API payload format - EXACT format from Postman documentation
            // monetaryValue must be an object with amount (string) and currency
            $payload = [
                'monetaryValue' => [
                    'amount' => (string)$amountInIQD, // Amount must be a STRING
                    'currency' => 'IQD', // Must be exactly 'IQD'
                ],
                'description' => $data['description'] ?? 'Payment',
            ];
            
            // statusCallbackUrl is optional but recommended
            if (isset($data['callback_url'])) {
                $payload['statusCallbackUrl'] = $data['callback_url'];
            }
            
            // Optional fields
            if (isset($data['redirectUri'])) {
                $payload['redirectUri'] = $data['redirectUri'];
            }
            if (isset($data['expiresIn'])) {
                $payload['expiresIn'] = $data['expiresIn'];
            }
            if (isset($data['category'])) {
                $payload['category'] = $data['category'];
            }
            
            // Log the payload being sent
            Log::info('FIB: Payment payload (monetaryValue format)', [
                'payload' => $payload,
                'alternative_amount_format' => $amountInFils,
            ]);
            
            // Log alternative formats for debugging
            Log::info('FIB: Trying amount in IQD format', [
                'payload' => $payload,
                'alternative_fils_format' => [
                    'amount' => $amountInFils,
                    'currency' => strtoupper($data['currency'] ?? 'IQD'),
                    'description' => $data['description'] ?? 'Payment',
                    'callback_url' => $data['callback_url'] ?? url('/api/payment/callback'),
                ],
            ]);
            
            // Log both formats for debugging
            Log::info('FIB: Amount conversion', [
                'amount_in_iqd' => $amountInIQD,
                'amount_in_fils' => $amountInFils,
                'original_format' => $data['amount'],
            ]);
            
            $endpoint = "{$this->baseUrl}/protected/v1/payments";
            
            Log::info('FIB: Payment request details', [
                'endpoint' => $endpoint,
                'amount_in_iqd' => $amountInIQD,
                'amount_in_fils' => $amountInFils,
                'payload' => $payload,
                'token_preview' => substr($token, 0, 20) . '...',
                'headers' => [
                    'Authorization' => 'Bearer ***',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = Http::withoutVerifying()
                ->withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            Log::info('FIB: Payment response received', [
                'status' => $response->status(),
                'status_text' => $response->status() === 200 ? 'Success' : ($response->status() === 400 ? 'Bad Request' : ($response->status() === 401 ? 'Unauthorized' : 'Unknown')),
                'response_headers' => $response->headers(),
                'body_length' => strlen($response->body()),
                'body_preview' => substr($response->body(), 0, 1000),
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json() ?? $response->body();
                
                // If monetaryValue format failed with 400, try alternative formats
                if ($response->status() === 400 && isset($payload['monetaryValue'])) {
                    Log::warning('FIB: monetaryValue format failed, trying alternative formats', [
                        'original_payload' => $payload,
                    ]);
                    
                    // Format 2: Try with amount as number instead of string
                    $retryPayload2 = [
                        'monetaryValue' => [
                            'amount' => $amountInIQD, // Try as number
                            'currency' => 'IQD',
                        ],
                        'description' => $data['description'] ?? 'Payment',
                    ];
                    
                    if (isset($data['callback_url'])) {
                        $retryPayload2['statusCallbackUrl'] = $data['callback_url'];
                    }
                    
                    $retryResponse2 = Http::withoutVerifying()
                        ->withToken($token)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($endpoint, $retryPayload2);
                    
                    if ($retryResponse2->successful()) {
                        Log::info('FIB: Retry with amount/callback_url format succeeded', [
                            'retry_payload' => $retryPayload2,
                        ]);
                        $response = $retryResponse2;
                        $responseData = $response->json();
                    } else {
                        // Format 3: Try with amount in fils (as string)
                        $retryPayload3 = [
                            'monetaryValue' => [
                                'amount' => (string)$amountInFils, // Try fils as string
                                'currency' => 'IQD',
                            ],
                            'description' => $data['description'] ?? 'Payment',
                        ];
                        
                        if (isset($data['callback_url'])) {
                            $retryPayload3['statusCallbackUrl'] = $data['callback_url'];
                        }
                        
                        // Format 4: Minimal payload without callback_url
                        $retryPayload4 = [
                            'monetaryValue' => [
                                'amount' => (string)$amountInIQD,
                                'currency' => 'IQD',
                            ],
                            'description' => $data['description'] ?? 'Payment',
                        ];
                        
                        $retryResponse3 = Http::withoutVerifying()
                            ->withToken($token)
                            ->withHeaders(['Content-Type' => 'application/json'])
                            ->post($endpoint, $retryPayload3);
                        
                        if ($retryResponse3->successful()) {
                            Log::info('FIB: Retry with fils format succeeded', [
                                'retry_payload' => $retryPayload3,
                            ]);
                            $response = $retryResponse3;
                            $responseData = $response->json();
                        } else {
                            // Try format 4: minimal payload without callback_url
                            $retryResponse4 = Http::withoutVerifying()
                                ->withToken($token)
                                ->withHeaders(['Content-Type' => 'application/json'])
                                ->post($endpoint, $retryPayload4);
                            
                            if ($retryResponse4->successful()) {
                                Log::info('FIB: Retry with minimal payload succeeded', [
                                    'retry_payload' => $retryPayload4,
                                ]);
                                $response = $retryResponse4;
                                $responseData = $response->json();
                            } else {
                                // All formats failed
                                Log::error('FIB Payment Creation Failed - All Formats Tried', [
                                    'format1_monetaryValue' => $payload,
                                    'format2_amount_iqd' => $retryPayload2,
                                    'format3_amount_fils' => $retryPayload3,
                                    'format4_minimal' => $retryPayload4,
                                    'last_error' => $retryResponse4->json() ?? $retryResponse4->body(),
                                ]);
                                
                                $errorBody = $retryResponse4->json() ?? $retryResponse4->body();
                                $errorMessage = 'Failed to create payment after trying all formats';
                                if (is_array($errorBody)) {
                                    if (isset($errorBody['errors']) && is_array($errorBody['errors'])) {
                                        $errorDetails = [];
                                        foreach ($errorBody['errors'] as $error) {
                                            $errorDetails[] = [
                                                'code' => $error['code'] ?? 'unknown',
                                                'title' => $error['title'] ?? 'unknown',
                                                'detail' => $error['detail'] ?? 'unknown',
                                            ];
                                        }
                                        $errorMessage .= ': ' . json_encode($errorDetails);
                                    } elseif (isset($errorBody['message'])) {
                                        $errorMessage .= ': ' . $errorBody['message'];
                                    }
                                }
                                
                                throw new Exception($errorMessage);
                            }
                        }
                    }
                } else {
                    // Non-400 error or not using monetaryValue format
                    Log::error('FIB Payment Creation Failed', [
                        'status' => $response->status(),
                        'full_body' => $response->body(),
                        'request_payload' => $payload,
                    ]);
                    
                    $errorMessage = 'Failed to create payment';
                    if (is_array($errorBody)) {
                        if (isset($errorBody['errors']) && is_array($errorBody['errors'])) {
                            $errorDetails = [];
                            foreach ($errorBody['errors'] as $error) {
                                $errorDetails[] = [
                                    'code' => $error['code'] ?? 'unknown',
                                    'title' => $error['title'] ?? 'unknown',
                                    'detail' => $error['detail'] ?? 'unknown',
                                ];
                            }
                            $errorMessage .= ': ' . json_encode($errorDetails);
                        } elseif (isset($errorBody['message'])) {
                            $errorMessage .= ': ' . $errorBody['message'];
                        }
                    } elseif (is_string($errorBody)) {
                        $errorMessage .= ': ' . $errorBody;
                    }
                    
                    throw new Exception($errorMessage);
                }
            }

            // Get response data (might be from retry)
            if (!isset($responseData)) {
                $responseData = $response->json();
            }
            
            Log::info('FIB: Payment created successfully - Full Response', [
                'full_response' => $responseData,
                'response_keys' => is_array($responseData) ? array_keys($responseData) : 'not_array',
                'payment_id' => $responseData['payment_id'] ?? $responseData['id'] ?? $responseData['data']['payment_id'] ?? $responseData['data']['id'] ?? 'not provided',
                'payment_url' => $responseData['payment_url'] ?? $responseData['url'] ?? $responseData['data']['payment_url'] ?? $responseData['data']['url'] ?? 'not provided',
                'has_qr_code' => isset($responseData['qr_code']) || isset($responseData['qrCode']) || isset($responseData['qr']) || isset($responseData['data']['qr_code']) || isset($responseData['data']['qrCode']),
            ]);

            // Normalize response structure - FIB API might return data in different formats
            $normalizedResponse = [];
            
            // Extract payment_id - FIB API returns 'paymentId' (camelCase)
            $normalizedResponse['payment_id'] = $responseData['paymentId'] ?? null;
            
            // Extract payment URLs - FIB API returns 'personalAppLink' and 'businessAppLink'
            // Use businessAppLink first, fallback to personalAppLink
            $normalizedResponse['payment_url'] = $responseData['businessAppLink'] 
                ?? $responseData['personalAppLink'] 
                ?? null;
            
            // Extract QR code - FIB API returns 'qrCode' (camelCase)
            $qrCode = $responseData['qrCode'] ?? null;
            
            // Extract readable code - FIB API returns 'readableCode'
            $readableCode = $responseData['readableCode'] ?? null;
            
            // Extract validUntil - FIB API returns 'validUntil'
            $validUntil = $responseData['validUntil'] ?? null;
            
            // Store QR code - FIB API returns qrCode as base64 image data
            if ($qrCode) {
                $normalizedResponse['qr_code'] = $qrCode;
            }
            
            // Store readable code and validUntil
            if ($readableCode) {
                $normalizedResponse['readable_code'] = $readableCode;
            }
            if ($validUntil) {
                $normalizedResponse['valid_until'] = $validUntil;
            }
            
            // Extract amount from response if available
            if (isset($responseData['amount']) && is_array($responseData['amount'])) {
                $normalizedResponse['amount'] = $responseData['amount']['amount'] ?? null;
            } elseif (isset($responseData['monetaryValue']) && is_array($responseData['monetaryValue'])) {
                $normalizedResponse['amount'] = $responseData['monetaryValue']['amount'] ?? null;
            }
            
            // Include any other fields from the response
            foreach ($responseData as $key => $value) {
                if (!isset($normalizedResponse[$key]) && !in_array($key, ['payment_id', 'id', 'payment_url', 'url', 'paymentUrl', 'qr_code', 'qrCode', 'qr', 'data'])) {
                    $normalizedResponse[$key] = $value;
                }
            }
            
            Log::info('FIB: Normalized response', [
                'normalized' => $normalizedResponse,
                'has_qr_code' => !empty($normalizedResponse['qr_code']),
            ]);

            return $normalizedResponse;
        } catch (Exception $e) {
            Log::error('FIB Payment Error - Complete Stack Trace', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'input_data' => $data,
                'base_url' => $this->baseUrl,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $paymentId): array
    {
        try {
            Log::info('FIB: Checking payment status', [
                'payment_id' => $paymentId,
                'base_url' => $this->baseUrl,
            ]);

            $token = $this->getAccessToken();

            $endpoint = "{$this->baseUrl}/protected/v1/payments/{$paymentId}/status";
            
            Log::info('FIB: Status check request details', [
                'endpoint' => $endpoint,
                'payment_id' => $paymentId,
                'token_preview' => substr($token, 0, 20) . '...',
            ]);

            $response = Http::withoutVerifying()
                ->withToken($token)
                ->get($endpoint);

            Log::info('FIB: Status check response received', [
                'status' => $response->status(),
                'status_text' => $response->status() === 200 ? 'Success' : ($response->status() === 404 ? 'Not Found' : 'Unknown'),
                'body_length' => strlen($response->body()),
                'body_preview' => substr($response->body(), 0, 500),
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json() ?? $response->body();
                
                Log::error('FIB Payment Status Check Failed - Complete Details', [
                    'status' => $response->status(),
                    'status_text' => $response->status() === 404 ? 'Payment Not Found' : ($response->status() === 401 ? 'Unauthorized' : 'Unknown Error'),
                    'full_body' => $response->body(),
                    'json_parsed' => $errorBody,
                    'payment_id' => $paymentId,
                    'endpoint' => $endpoint,
                    'response_headers' => $response->headers(),
                ]);
                
                throw new Exception('Failed to check payment status: ' . $response->body());
            }

            $responseData = $response->json();
            
            Log::info('FIB: Payment status retrieved successfully', [
                'payment_id' => $paymentId,
                'status' => $responseData['status'] ?? 'unknown',
                'full_response' => $responseData,
            ]);

            return $responseData;
        } catch (Exception $e) {
            Log::error('FIB Status Check Error - Complete Stack Trace', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'payment_id' => $paymentId,
                'base_url' => $this->baseUrl,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a payment
     */
    public function cancelPayment(string $paymentId): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withoutVerifying()
                ->withToken($token)
                ->post("{$this->baseUrl}/protected/v1/payments/{$paymentId}/cancel");

            if (!$response->successful()) {
                Log::error('FIB Payment Cancellation Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payment_id' => $paymentId,
                ]);
                throw new Exception('Failed to cancel payment: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FIB Cancel Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

