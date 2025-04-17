<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 * name="Payment",
 * description="API endpoints for handling payments with security considerations"
 * )
 */
class PaymentController extends Controller
{
    /**
     * Initiate a payment transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:10',
            'payment_gateway' => 'required|string|in:paystack,flutterwave,stripe,manual_transfer',
            'item_id' => 'required|integer',
            'item_type' => 'required|string',
            'description' => 'nullable|string|max:255',
            'callback_url' => 'nullable|url',
            // Add more specific validation based on the gateway if needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paymentService = PaymentService::resolve($request->payment_gateway);

        if (!$paymentService) {
            return response()->json(['message' => 'Payment gateway not supported'], 400);
        }

        $transactionReference = Str::uuid(); // Generate a unique transaction reference in your system

        try {
            $transaction = DB::transaction(function () use ($request, $paymentService, $transactionReference) {
                return Transaction::create([
                    'transaction_reference' => $transactionReference,
                    'payable_id' => $request->item_id,
                    'payable_type' => $request->item_type,
                    'user_id' => $request->user()->id,
                    'payment_gateway' => $request->payment_gateway,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'status' => 'pending',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error creating transaction: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to initiate payment, please try again.'], 500);
        }

        $paymentData = [
            'amount' => $request->amount * 100, // Convert to cents/smallest unit
            'currency' => $request->currency,
            'reference' => $transactionReference, // Use your unique reference
            'email' => $request->user()->email, // Ensure user is authenticated
            'callback_url' => $request->callback_url ?? route('payment.verify', $transactionReference),
            'metadata' => ['transaction_id' => $transaction->id, 'item_id' => $request->item_id, 'item_type' => $request->item_type],
            'description' => $request->description,
            // Add other gateway-specific parameters securely
        ];

        $gatewayResponse = $paymentService->initiatePayment($paymentData);

        if (isset($gatewayResponse['status']) && ($gatewayResponse['status'] === true || $gatewayResponse['status'] === 'success')) {
            $transaction->update(['gateway_reference' => $gatewayResponse['data']['reference'] ?? $gatewayResponse['data']['id'] ?? null]);
            return response()->json($gatewayResponse['data']); // Return gateway-specific data for redirection
        } else {
            $transaction->update(['status' => 'failed', 'gateway_response' => json_encode($gatewayResponse)]);
            Log::error('Payment initiation failed for transaction ' . $transactionReference . ': ' . json_encode($gatewayResponse));
            return response()->json(['message' => 'Payment initiation failed', 'details' => $gatewayResponse], 400);
        }
    }

    /**
     * Verify a payment transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $reference Your application's transaction reference
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, string $reference): JsonResponse
    {
        $transaction = Transaction::where('transaction_reference', $reference)->firstOrFail();
        $paymentService = PaymentService::resolve($transaction->payment_gateway);

        if (!$paymentService) {
            return response()->json(['message' => 'Payment gateway not supported'], 400);
        }

        $verificationResponse = $paymentService->verifyPayment($transaction->gateway_reference ?? $reference);

        if (isset($verificationResponse['status']) && ($verificationResponse['status'] === true || $verificationResponse['status'] === 'success')) {
            // Consistent use of database transaction for atomicity
            try {
                DB::transaction(function () use ($transaction, $verificationResponse) {
                    $transaction->update([
                        'status' => 'successful',
                        'gateway_response' => json_encode($verificationResponse),
                        'paid_at' => now(),
                    ]);
                    // Update your order/booking status based on successful payment
                    // Example: Order::where('id', $transaction->payable_id)->update(['payment_status' => 'paid']);
                });
                return response()->json(['message' => 'Payment successful', 'data' => $verificationResponse['data']]);
            } catch (\Exception $e) {
                Log::error('Error updating transaction status after successful verification ' . $transaction->transaction_reference . ': ' . $e->getMessage());
                return response()->json(['message' => 'Payment successful, but failed to update internal status. Please contact support.'], 500);
            }
        } else {
            $transaction->update(['status' => 'failed', 'gateway_response' => json_encode($verificationResponse)]);
            Log::error('Payment verification failed for transaction ' . $transaction->transaction_reference . ': ' . json_encode($verificationResponse));
            return response()->json(['message' => 'Payment verification failed', 'details' => $verificationResponse], 400);
        }
    }

    /**
     * Handle payment gateway webhooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $gateway
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request, string $gateway): JsonResponse
    {
        $paymentService = PaymentService::resolve($gateway);

        if (!$paymentService) {
            return response()->json(['message' => 'Payment gateway not supported'], 400);
        }

        try {
            $webhookData = $paymentService->handleWebhook($request);
        } catch (\Exception $e) {
            Log::error('Webhook handling failed for ' . $gateway . ': ' . $e->getMessage());
            return response()->json(['message' => 'Invalid webhook data'], 400);
        }

        // Process webhook data securely and update transaction status
        if (isset($webhookData['data']['reference'])) {
            $transaction = Transaction::where('gateway_reference', $webhookData['data']['reference'])->orWhere('transaction_reference', $webhookData['data']['reference'])->first();

            if ($transaction) {
                try {
                    DB::transaction(function () use ($transaction, $webhookData) {
                        if (isset($webhookData['event']) && $webhookData['event'] === 'charge.success') {
                            $transaction->update(['status' => 'successful', 'gateway_response' => json_encode($webhookData), 'paid_at' => now()]);
                            // Update order/booking status
                        } elseif (isset($webhookData['event']) && in_array($webhookData['event'], ['charge.failed', 'charge.error'])) {
                            $transaction->update(['status' => 'failed', 'gateway_response' => json_encode($webhookData)]);
                        }
                        // Add more webhook event handling as needed
                    });
                    return response()->json(['message' => 'Webhook received and processed']);
                } catch (\Exception $e) {
                    Log::error('Error updating transaction status from webhook ' . $transaction->transaction_reference . ': ' . $e->getMessage());
                    return response()->json(['message' => 'Webhook received, but failed to update internal status.'], 500);
                }
            } else {
                Log::warning('Webhook received for unknown transaction reference: ' . $webhookData['data']['reference'] ?? 'N/A');
                return response()->json(['message' => 'Webhook received for unknown transaction'], 200); // Don't expose potential vulnerabilities
            }
        }

        return response()->json(['message' => 'Webhook received'], 200);
    }
}