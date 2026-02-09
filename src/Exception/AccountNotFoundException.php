<?php

namespace App\Exception;

final class AccountNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Account not found: {$id}");
    }
}
