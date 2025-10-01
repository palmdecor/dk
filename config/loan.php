<?php

return [
    'default_interest_rate' => env('LOAN_DEFAULT_INTEREST', 2.29),
    'max_amount' => env('LOAN_MAX_AMOUNT', 500000),
    'min_amount' => env('LOAN_MIN_AMOUNT', 1000),
];
