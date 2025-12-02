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
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    use ApiResponse;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a new payment
     */
    public function store(Request $request)
    {
        Log::info('PaymentController: Payment creation request received', [
            'user_id' => $request->user()->id,
            'request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:255',
        ]);

        Log::info('PaymentController: Request validated', [
            'validated_data' => $validated,
        ]);

        try {
            $user = $request->user();
            
            Log::info('PaymentController: Creating local payment record', [
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'IQD',
            ]);
            
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'IQD',
                'description' => $validated['description'] ?? 'Payment',
                'status' => 'pending',
                'callback_url' => url('/api/payment/callback'),
            ]);

            Log::info('PaymentController: Local payment record created', [
                'payment_id' => $payment->id,
                'local_payment_id' => $payment->id,
            ]);

            // Create payment with FIB
            $fibData = [
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'IQD',
                'description' => $validated['description'] ?? 'Payment',
                'callback_url' => url('/api/payment/callback'),
            ];

            Log::info('PaymentController: Calling FIB PaymentService', [
                'fib_data' => $fibData,
                'callback_url' => $fibData['callback_url'],
            ]);

            try {
                $fibResponse = $this->paymentService->createPayment($fibData);

                Log::info('PaymentController: FIB payment created successfully', [
                    'fib_response' => $fibResponse,
                    'has_payment_id' => isset($fibResponse['payment_id']),
                    'has_payment_url' => isset($fibResponse['payment_url']),
                    'has_qr_code' => isset($fibResponse['qr_code']),
                ]);

                // Update payment with FIB response
                $payment->update([
                    'payment_id' => $fibResponse['payment_id'] ?? null,
                    'payment_url' => $fibResponse['payment_url'] ?? null,
                    'qr_code' => $fibResponse['qr_code'] ?? null,
                    'fib_response' => $fibResponse,
                    'status' => 'processing',
                ]);
            } catch (\Exception $fibError) {
                // If FIB API fails, keep payment as pending but still return success
                // This allows the user to see their payment and retry later
                Log::warning('PaymentController: FIB payment creation failed, but keeping local payment record', [
                    'error' => $fibError->getMessage(),
                    'payment_id' => $payment->id,
                    'fib_error_class' => get_class($fibError),
                ]);
                
                // Keep payment as pending - user can retry later
                $payment->update([
                    'status' => 'pending',
                    'fib_response' => [
                        'error' => $fibError->getMessage(),
                        'error_type' => 'FIB_API_ERROR',
                        'note' => 'Payment created locally but FIB gateway integration failed. This may be due to: callback URL (localhost not accepted), missing API fields, or API configuration issues.',
                    ],
                ]);
            }

            Log::info('PaymentController: Payment updated with FIB response', [
                'payment_id' => $payment->id,
                'fib_payment_id' => $payment->payment_id,
                'status' => $payment->status,
            ]);

            // Refresh payment to get latest status
            $payment->refresh();
            
            return $this->success('Payment created successfully', [
                'id' => $payment->id,
                'payment_id' => $payment->payment_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'payment_url' => $payment->payment_url,
                'qr_code' => $payment->qr_code,
                'note' => $payment->status === 'pending' && isset($payment->fib_response['error']) 
                    ? 'Payment created locally but FIB gateway integration failed. Possible issues: callback URL (localhost not accepted), missing API fields, or API configuration. Contact support or check FIB API documentation.' 
                    : null,
            ], 201);
        } catch (\Exception $e) {
            Log::error('PaymentController: Payment creation failed - Complete Error Details', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'user_id' => $request->user()->id,
                'validated_data' => $validated ?? null,
                'stack_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->error('Failed to create payment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get user's payments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->success('Payments retrieved successfully', $payments);
    }

    /**
     * Get a specific payment
     */
    public function show(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return $this->error('Unauthorized', null, 403);
        }

        return $this->success('Payment retrieved successfully', [
            'id' => $payment->id,
            'payment_id' => $payment->payment_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'description' => $payment->description,
            'status' => $payment->status,
            'payment_url' => $payment->payment_url,
            'qr_code' => $payment->qr_code,
            'created_at' => $payment->created_at,
            'paid_at' => $payment->paid_at,
        ]);
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request, Payment $payment)
    {
        $user = $request->user();

        Log::info('PaymentController: Checking payment status', [
            'user_id' => $user->id,
            'payment_id' => $payment->id,
            'fib_payment_id' => $payment->payment_id,
            'current_status' => $payment->status,
        ]);

        if ($payment->user_id !== $user->id) {
            Log::warning('PaymentController: Unauthorized status check attempt', [
                'user_id' => $user->id,
                'payment_user_id' => $payment->user_id,
                'payment_id' => $payment->id,
            ]);
            return $this->error('Unauthorized', null, 403);
        }

        // If payment doesn't have FIB payment_id, just return current status
        if (!$payment->payment_id) {
            Log::info('PaymentController: Payment has no FIB payment_id, returning current status', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'fib_response' => $payment->fib_response,
            ]);
            
            // Refresh payment from database to get latest status
            $payment->refresh();
            
            // JUST GET THE URL DIRECTLY FROM DATABASE - NO EXTRACTION NEEDED
            Log::info('PaymentController: Returning payment from database', [
                'payment_id' => $payment->id,
                'payment_url_from_db' => $payment->payment_url,
                'qr_code_from_db' => $payment->qr_code ? 'EXISTS' : 'NULL',
            ]);
            
            return $this->success('Payment status retrieved successfully', [
                'id' => $payment->id,
                'payment_id' => $payment->payment_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'description' => $payment->description,
                'status' => $payment->status,
                'payment_url' => $payment->payment_url, // DIRECTLY FROM DATABASE
                'qr_code' => $payment->qr_code, // DIRECTLY FROM DATABASE
                'created_at' => $payment->created_at,
                'paid_at' => $payment->paid_at,
                'note' => $payment->payment_id ? null : 'Payment created but FIB integration pending. FIB payment gateway needs to be configured.',
            ]);
        }

        try {
            Log::info('PaymentController: Calling FIB to check payment status', [
                'fib_payment_id' => $payment->payment_id,
            ]);

            $fibStatus = $this->paymentService->checkPaymentStatus($payment->payment_id);
            
            Log::info('PaymentController: FIB status response received', [
                'fib_status' => $fibStatus,
                'fib_status_value' => $fibStatus['status'] ?? 'unknown',
            ]);
            
            // Map FIB status to our status
            $statusMap = [
                'PAID' => 'completed',
                'PENDING' => 'processing',
                'FAILED' => 'failed',
                'CANCELLED' => 'cancelled',
            ];

            $oldStatus = $payment->status;
            $newStatus = $statusMap[$fibStatus['status']] ?? $payment->status;
            
            Log::info('PaymentController: Status mapping', [
                'old_status' => $oldStatus,
                'fib_status' => $fibStatus['status'] ?? 'unknown',
                'new_status' => $newStatus,
                'status_changed' => $oldStatus !== $newStatus,
            ]);
            
            $updateData = [
                'status' => $newStatus,
                'fib_response' => array_merge($payment->fib_response ?? [], $fibStatus),
            ];

            if ($newStatus === 'completed' && !$payment->paid_at) {
                $updateData['paid_at'] = now();
                Log::info('PaymentController: Setting paid_at timestamp', [
                    'paid_at' => $updateData['paid_at'],
                ]);
            }

            $payment->update($updateData);

            Log::info('PaymentController: Payment status updated in database', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'update_data' => $updateData,
            ]);

            // Refresh payment to get updated status
            $payment->refresh();
            
            // JUST GET THE URL DIRECTLY FROM DATABASE - NO EXTRACTION NEEDED
            Log::info('PaymentController: Returning payment from database', [
                'payment_id' => $payment->id,
                'payment_url_from_db' => $payment->payment_url,
                'qr_code_from_db' => $payment->qr_code ? 'EXISTS' : 'NULL',
                'final_status' => $payment->status,
            ]);
            
            return $this->success('Payment status retrieved successfully', [
                'id' => $payment->id,
                'payment_id' => $payment->payment_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'description' => $payment->description,
                'status' => $payment->status,
                'payment_url' => $payment->payment_url, // DIRECTLY FROM DATABASE
                'qr_code' => $payment->qr_code, // DIRECTLY FROM DATABASE
                'created_at' => $payment->created_at,
                'paid_at' => $payment->paid_at,
                'fib_status' => $fibStatus['status'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController: Payment status check failed - Complete Error Details', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'payment_id' => $payment->id,
                'fib_payment_id' => $payment->payment_id,
                'current_status' => $payment->status,
                'stack_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->error('Failed to check payment status: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Retry/reprocess a payment with FIB
     */
    public function retry(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return $this->error('Unauthorized', null, 403);
        }

        // Only allow retry for pending payments without FIB payment_id
        if ($payment->payment_id) {
            return $this->error('Payment already has FIB payment ID. Cannot retry.', null, 400);
        }

        if ($payment->status !== 'pending') {
            return $this->error('Only pending payments can be retried', null, 400);
        }

        try {
            Log::info('PaymentController: Retrying payment with FIB', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);

            // Create payment with FIB using existing payment data
            // Convert amount to float/numeric to ensure proper format
            $amount = is_numeric($payment->amount) ? (float)$payment->amount : 0;
            
            $fibData = [
                'amount' => $amount,
                'currency' => $payment->currency,
                'description' => $payment->description ?? 'Payment',
                'callback_url' => $payment->callback_url ?? url('/api/payment/callback'),
                'recipient_phone' => '7301111215', // FIB test account phone number (without +964 prefix)
            ];

            try {
                $fibResponse = $this->paymentService->createPayment($fibData);

                Log::info('PaymentController: FIB payment retry successful', [
                    'payment_id' => $payment->id,
                    'fib_response' => $fibResponse,
                    'has_payment_id' => isset($fibResponse['payment_id']),
                ]);

                // Update payment with FIB response
                $payment->update([
                    'payment_id' => $fibResponse['payment_id'] ?? null,
                    'payment_url' => $fibResponse['payment_url'] ?? null,
                    'qr_code' => $fibResponse['qr_code'] ?? null,
                    'fib_response' => $fibResponse,
                    'status' => 'processing',
                ]);

                return $this->success('Payment reprocessed successfully', [
                    'id' => $payment->id,
                    'payment_id' => $payment->payment_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'payment_url' => $payment->payment_url,
                    'qr_code' => $payment->qr_code,
                ]);
            } catch (\Exception $fibError) {
                // If FIB API fails again, update error but keep payment
                Log::warning('PaymentController: FIB payment retry failed', [
                    'error' => $fibError->getMessage(),
                    'error_class' => get_class($fibError),
                    'payment_id' => $payment->id,
                    'stack_trace' => $fibError->getTraceAsString(),
                ]);
                
                try {
                    // Get current fib_response and ensure it's an array
                    $currentFibResponse = $payment->fib_response ?? [];
                    if (!is_array($currentFibResponse)) {
                        $currentFibResponse = [];
                    }
                    
                    $payment->update([
                        'status' => 'pending',
                        'fib_response' => array_merge($currentFibResponse, [
                            'retry_error' => $fibError->getMessage(),
                            'retry_attempted_at' => now()->toDateTimeString(),
                        ]),
                    ]);
                    
                    // Refresh payment to get updated status
                    $payment->refresh();
                } catch (\Exception $updateError) {
                    Log::error('PaymentController: Failed to update payment after retry failure', [
                        'update_error' => $updateError->getMessage(),
                        'update_error_class' => get_class($updateError),
                        'payment_id' => $payment->id,
                        'stack_trace' => $updateError->getTraceAsString(),
                    ]);
                    // Continue even if update fails
                }

                // Return error response with payment info
                // Use payment status or default to pending
                try {
                    $paymentStatus = $payment->status ?? 'pending';
                    $paymentId = $payment->id;
                } catch (\Exception $e) {
                    $paymentStatus = 'pending';
                    $paymentId = $payment->id ?? null;
                    Log::warning('PaymentController: Error accessing payment properties after retry failure', [
                        'error' => $e->getMessage(),
                        'payment_id' => $paymentId,
                    ]);
                }
                
                $errorMessage = 'Failed to reprocess payment: ' . $fibError->getMessage();
                
                Log::info('PaymentController: Returning error response for retry', [
                    'payment_id' => $paymentId,
                    'status' => $paymentStatus,
                    'error_message' => $errorMessage,
                ]);
                
                return $this->error($errorMessage, [
                    'id' => $paymentId,
                    'status' => $paymentStatus,
                    'note' => 'FIB payment gateway integration failed. This may be due to: callback URL (localhost not accepted), missing API fields, or API configuration issues.',
                ], 500);
            }
        } catch (\Exception $e) {
            $paymentId = null;
            $paymentStatus = 'pending';
            
            try {
                $paymentId = $payment->id ?? null;
                $paymentStatus = $payment->status ?? 'pending';
            } catch (\Exception $accessError) {
                Log::warning('PaymentController: Could not access payment properties in outer catch', [
                    'access_error' => $accessError->getMessage(),
                ]);
            }
            
            Log::error('PaymentController: Payment retry failed - Complete Error', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'payment_id' => $paymentId,
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to retry payment: ' . $e->getMessage(), [
                'id' => $paymentId,
                'status' => $paymentStatus,
            ], 500);
        }
    }

    /**
     * Manually update payment status (for testing/development)
     * Only works in non-production environments
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        // Only allow in non-production environments
        if (app()->environment('production')) {
            return $this->error('This feature is not available in production', null, 403);
        }

        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return $this->error('Unauthorized', null, 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,cancelled',
        ]);

        try {
            $oldStatus = $payment->status;
            $newStatus = $validated['status'];

            $updateData = [
                'status' => $newStatus,
            ];

            // If marking as completed, set paid_at timestamp
            if ($newStatus === 'completed' && !$payment->paid_at) {
                $updateData['paid_at'] = now();
            }

            // If moving away from completed, clear paid_at
            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                $updateData['paid_at'] = null;
            }

            $payment->update($updateData);

            // If payment is completed, activate associated subscription
            if ($newStatus === 'completed') {
                $subscription = \App\Models\Subscription::where('payment_id', $payment->id)->first();
                
                if ($subscription && $subscription->type === 'verified') {
                    DB::beginTransaction();
                    try {
                        $subscription->activate();
                        
                        // Update user verified status
                        $payment->user->update([
                            'is_verified' => true,
                            'verified_at' => now(),
                        ]);
                        
                        DB::commit();
                        
                        Log::info('Verified subscription activated via manual status update', [
                            'user_id' => $payment->user_id,
                            'subscription_id' => $subscription->id,
                            'payment_id' => $payment->id,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to activate subscription via manual status update', [
                            'error' => $e->getMessage(),
                            'subscription_id' => $subscription->id,
                        ]);
                    }
                }
            }

            Log::info('PaymentController: Payment status manually updated', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'environment' => app()->environment(),
            ]);

            return $this->success('Payment status updated successfully', [
                'id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'paid_at' => $payment->paid_at,
                'note' => 'Status updated manually (development/testing mode)',
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController: Failed to manually update payment status', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return $this->error('Failed to update payment status: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Cancel a payment
     */
    public function cancel(Request $request, Payment $payment)
    {
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return $this->error('Unauthorized', null, 403);
        }

        if (!$payment->payment_id) {
            return $this->error('Payment ID not found. Cannot cancel payment without FIB payment ID.', null, 400);
        }

        if (in_array($payment->status, ['completed', 'cancelled'])) {
            return $this->error('Payment cannot be cancelled', null, 400);
        }

        try {
            $fibResponse = $this->paymentService->cancelPayment($payment->payment_id);
            
            $payment->update([
                'status' => 'cancelled',
                'fib_response' => array_merge($payment->fib_response ?? [], $fibResponse),
            ]);

            return $this->success('Payment cancelled successfully', [
                'id' => $payment->id,
                'status' => $payment->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment cancellation failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return $this->error('Failed to cancel payment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Handle FIB payment callback
     */
    public function callback(Request $request)
    {
        try {
            $paymentId = $request->input('payment_id');
            $status = $request->input('status');

            if (!$paymentId) {
                Log::warning('Payment callback received without payment_id', $request->all());
                return response()->json(['error' => 'Payment ID required'], 400);
            }

            $payment = Payment::where('payment_id', $paymentId)->first();

            if (!$payment) {
                Log::warning('Payment not found for callback', ['payment_id' => $paymentId]);
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Map FIB status to our status
            $statusMap = [
                'PAID' => 'completed',
                'PENDING' => 'processing',
                'FAILED' => 'failed',
                'CANCELLED' => 'cancelled',
            ];

            $newStatus = $statusMap[$status] ?? $payment->status;

            $updateData = [
                'status' => $newStatus,
                'fib_response' => array_merge($payment->fib_response ?? [], $request->all()),
            ];

            if ($newStatus === 'completed' && !$payment->paid_at) {
                $updateData['paid_at'] = now();
            }

            $payment->update($updateData);

            // If payment is completed, activate associated subscription
            if ($newStatus === 'completed') {
                $subscription = Subscription::where('payment_id', $payment->id)->first();
                
                if ($subscription && $subscription->type === 'verified') {
                    DB::beginTransaction();
                    try {
                        $subscription->activate();
                        
                        // Update user verified status
                        $payment->user->update([
                            'is_verified' => true,
                            'verified_at' => now(),
                        ]);
                        
                        DB::commit();
                        
                        Log::info('Verified subscription activated', [
                            'user_id' => $payment->user_id,
                            'subscription_id' => $subscription->id,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to activate subscription', [
                            'error' => $e->getMessage(),
                            'subscription_id' => $subscription->id,
                        ]);
                    }
                }
            }

            Log::info('Payment callback processed', [
                'payment_id' => $paymentId,
                'status' => $newStatus,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }
}
