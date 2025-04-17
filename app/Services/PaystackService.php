<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackService implements PaymentServiceInterface
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function initiatePayment(array $paymentData): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', $paymentData);

        return $response->json();
    }

    public function verifyPayment(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        return $response->json();
    }

    public function handleWebhook(Request $request): array
    {
        $signature = $request->header('x-paystack-signature');
        $payload = json_encode($request->all());
        $expectedSignature = hash_hmac('sha512', $payload, $this->secretKey);

        if ($signature !== $expectedSignature) {
            return ['status' => 'error', 'message' => 'Invalid Paystack webhook signature'];
        }

        return $request->all();
    }
}