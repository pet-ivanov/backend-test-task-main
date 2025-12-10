<?php

namespace App\Service;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

interface PaymentServiceInterface
{
    public function processPayment(float $amount, string $processorName): bool;
}

class PaymentService implements PaymentServiceInterface
{
    public function processPayment(float $amount, string $processorName): bool
    {
        switch ($processorName) {
            case 'paypal':
                $processor = new PaypalPaymentProcessor();
                try {
                    $processor->pay((int)$amount);
                    return true;
                } catch (\Exception $e) {
                    error_log("PayPal ошибка платежа: " . $e->getMessage());
                    return false;
                }

            case 'stripe':
                $processor = new StripePaymentProcessor();
                try {
                    $processor->processPayment((int)$amount);
                    return true;
                } catch (\Exception $e) {
                    error_log("Stripe ошибка платежа: " . $e->getMessage());
                    return false;
                }

            default:
                throw new \InvalidArgumentException('Неподдерживаемый платежный сервис');
        }
    }
}