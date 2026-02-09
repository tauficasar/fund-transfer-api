<?php

namespace App\Exception;

final class InvalidTransferException extends ApiException
{
    public static function accountUnavailable(): self
    {
        return new self(
            'Account no longer available',
            'ACCOUNT_NOT_AVAILABLE',
            409
        );
    }

    public static function sameAccount(): self
    {
        return new self(
            'Source and destination must differ',
            'SAME_ACCOUNT_TRANSFER',
            422
        );
    }

    public static function currencyMismatch(): self
    {
        return new self(
            'Account currency must match transfer currency',
            'CURRENCY_MISMATCH',
            422
        );
    }
    
    public static function transferFailed(): self
    {
        return new self(
            'Transfer Failed',
            'TRANSFER_FAILED',
            422
        );
    }

    public static function transferNotFound(string $id): self
    {
        return new self(
            "Transfer not found: {$id}",
            'TRANSFER_NOT_FOUND',
            404,
        );
    }
    
}
