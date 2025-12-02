<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    use ApiResponse;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Purchase verified badge subscription
     */
    public function purchaseVerified(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();

            // Check if user already has active verified subscription
            if ($user->activeVerifiedSubscription()) {
                return $this->error('You already have an active verified badge', null, 400);
            }

            // Create payment record - amount will be set from FIB response
            // Note: FIB API requires an amount, so we'll send a minimum (1 IQD) and let FIB determine actual amount
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => 1, // Minimum amount - will be updated from FIB response
                'currency' => 'IQD',
                'description' => 'Verified Badge Subscription',
                'status' => 'pending',
                'callback_url' => url('/api/payment/callback'),
            ]);

            // Create payment with FIB - FIB API requires amount field
            // Using test account phone: 7301111215 (without country code prefix)
            // Amount: 1 IQD minimum (FIB will determine actual amount based on subscription type)
            $fibData = [
                'amount' => 1, // Minimum amount required by FIB API
                'currency' => 'IQD',
                'description' => 'Verified Badge Subscription',
                'callback_url' => url('/api/payment/callback'),
                'recipient_phone' => '7301111215', // FIB test account phone number (without +964 prefix)
            ];

            try {
                Log::info('SubscriptionController: Calling FIB PaymentService for subscription', [
                    'payment_id' => $payment->id,
                    'fib_data' => $fibData,
                ]);

                $fibResponse = $this->paymentService->createPayment($fibData);

                Log::info('SubscriptionController: FIB payment created successfully', [
                    'payment_id' => $payment->id,
                    'fib_response' => $fibResponse,
                    'has_payment_id' => isset($fibResponse['payment_id']),
                ]);

                // Update payment with FIB response
                // Extract payment URL from various possible locations in FIB response
                $paymentUrl = $fibResponse['payment_url'] 
                    ?? $fibResponse['businessAppLink'] 
                    ?? $fibResponse['personalAppLink']
                    ?? null;
                
                // Extract QR code (base64 image) from FIB response
                $qrCode = $fibResponse['qr_code'] 
                    ?? $fibResponse['qrCode']
                    ?? null;
                
                $payment->update([
                    'payment_id' => $fibResponse['payment_id'] ?? null,
                    'payment_url' => $paymentUrl, // Store the payment URL
                    'qr_code' => $qrCode, // Store QR code (base64 image)
                    'amount' => $fibResponse['amount'] ?? $payment->amount, // Update amount from FIB if provided
                    'fib_response' => $fibResponse, // Store full response for reference
                    'status' => 'processing',
                ]);
                
                Log::info('SubscriptionController: Payment updated with URL and QR code', [
                    'payment_id' => $payment->id,
                    'has_payment_url' => !empty($paymentUrl),
                    'has_qr_code' => !empty($qrCode),
                    'payment_url' => $paymentUrl ? substr($paymentUrl, 0, 50) . '...' : 'null',
                ]);

                Log::info('SubscriptionController: Payment updated with FIB response', [
                    'payment_id' => $payment->id,
                    'fib_payment_id' => $payment->payment_id,
                    'status' => $payment->status,
                ]);
            } catch (\Exception $fibError) {
                // If FIB API fails, keep payment as pending but still create subscription
                Log::error('SubscriptionController: FIB payment creation failed - Complete Error Details', [
                    'error_message' => $fibError->getMessage(),
                    'error_class' => get_class($fibError),
                    'error_code' => $fibError->getCode(),
                    'payment_id' => $payment->id,
                    'fib_data' => $fibData,
                    'stack_trace' => $fibError->getTraceAsString(),
                    'file' => $fibError->getFile(),
                    'line' => $fibError->getLine(),
                ]);
                
                // Keep payment as pending - user can retry later
                $payment->update([
                    'status' => 'pending',
                    'fib_response' => ['error' => $fibError->getMessage()],
                ]);

                Log::info('SubscriptionController: Payment kept as pending due to FIB error', [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                ]);
            }

            // Create subscription record
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'type' => 'verified',
                'amount' => $payment->amount, // Amount from payment (set by FIB)
                'status' => 'pending',
            ]);

            DB::commit();

            // Return response even if FIB failed - payment and subscription are created
            return $this->success('Subscription payment created successfully', [
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'payment_url' => $payment->payment_url,
                'qr_code' => $payment->qr_code, // This is the URL that user needs to open
                'status' => $payment->status,
                'amount' => $payment->amount,
                'note' => $payment->status === 'pending' ? 'FIB payment creation failed. Please try again later or contact support.' : 'Open the URL/QR code to complete payment with credentials: Phone: 7301111215, Password: Personal@123, OTP: 123-456',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription purchase failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return $this->error('Failed to create subscription: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get user's subscriptions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $subscriptions = Subscription::where('user_id', $user->id)
            ->with('payment')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success('Subscriptions retrieved successfully', $subscriptions);
    }

    /**
     * Get user's active verified subscription
     */
    public function getVerifiedStatus(Request $request)
    {
        $user = $request->user();
        
        $subscription = $user->activeVerifiedSubscription();
        
        return $this->success('Verified status retrieved successfully', [
            'is_verified' => $user->is_verified,
            'has_active_subscription' => $subscription !== null,
            'subscription' => $subscription,
        ]);
    }
}
