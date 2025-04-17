<?php

namespace App\Services;

interface PaymentServiceInterface
{
    public function initiatePayment(array $paymentData): array;
    public function verifyPayment(string $reference): array;
    public function handleWebhook(Request $request): array;
}