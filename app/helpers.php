<?php

if (! function_exists('format_currency')) {
    function format_currency(float $amount, string $currency = '₺'): string
    {
        return $currency . ' ' . number_format($amount, 2, ',', '.');
    }
}

if (! function_exists('format_percentage')) {
    function format_percentage(float $value): string
    {
        return number_format($value, 2, ',', '.') . '%';
    }
}
