<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class PaymentService
{
    public static function resolve(string $gateway): ?PaymentServiceInterface
    {
        $gateway = strtolower($gateway);
        $className = __NAMESPACE__ . '\\' . ucfirst($gateway) . 'Service';

        if (class_exists($className)) {
            return App::make($className);
        }

        return null;
    }
}