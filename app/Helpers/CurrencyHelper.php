<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    /**
     * Get the current currency code
     * @return string
     */
    public static function getCurrencyCode()
    {
        return Setting::get('currency', 'INR');
    }

    /**
     * Get the current currency symbol
     * @return string
     */
    public static function getCurrencySymbol()
    {
        $currency = self::getCurrencyCode();
        $currencies = self::getAvailableCurrencies();

        return $currencies[$currency]['symbol'] ?? Setting::get('currency_symbol', "\u{20B9}");
    }

    /**
     * Format amount with currency symbol
     * @param float $amount
     * @param int $decimals
     * @return string
     */
    public static function formatCurrency($amount, $decimals = 2)
    {
        return self::getCurrencySymbol() . number_format($amount, $decimals);
    }

    /**
     * Get the Font Awesome icon class for the current currency.
     * @return string
     */
    public static function getCurrencyIconClass()
    {
        return self::getCurrencyCode() === 'USD' ? 'fa-dollar-sign' : 'fa-indian-rupee-sign';
    }

    /**
     * Get all available currencies
     * @return array
     */
    public static function getAvailableCurrencies()
    {
        return [
            'INR' => ['name' => 'Indian Rupee', 'symbol' => "\u{20B9}"],
            'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
        ];
    }
}
