<?php

namespace App\Exception;

final class InsufficientBalanceException extends ApiException
{
    public function __construct(string $balance, string $amount)
    {
        parent::__construct(
            sprintf('Insufficient balance. Available %s, requested %s', $balance, $amount),
            'INSUFFICIENT_BALANCE',
            422
        );
    }
}
