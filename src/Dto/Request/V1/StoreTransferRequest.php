<?php

namespace App\Dto\Request\V1;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class StoreTransferRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'from_account_id is required')]
        #[Assert\Uuid]
        public string $fromAccountId,

        #[Assert\NotBlank(message: 'to_account_id is required')]
        #[Assert\Uuid]
        public string $toAccountId,

        #[Assert\NotBlank(message: 'amount is required')]
        #[Assert\Type(type: 'numeric', message: 'amount must be a numeric value')]
        #[Assert\Positive(message: 'amount must be greater than zero')]
        #[Assert\Regex(
            pattern: '/^\d+(\.\d{1,2})?$/',
            message: 'amount can have at most 2 decimal places'
        )]
        #[Assert\LessThanOrEqual(
            value: '9999999999999999.99',
            message: 'amount exceeds maximum allowed value'
        )]
        public string $amount,

        #[Assert\NotBlank(message: 'currency is required')]
        #[Assert\Length(exactly: 3)]
        #[Assert\Regex(
            pattern: '/^[A-Z]{3}$/', 
            message: 'currency must be a 3-letter ISO code'
        )]
        public string $currency,

        #[Assert\NotBlank(message: 'idempotency_key is required')]
        #[Assert\Length(max: 64)]
        #[Assert\Regex(
            pattern: '/^[a-zA-Z0-9\-_]+$/', 
            message: 'idempotency_key must be alphanumeric with optional hyphens/underscores'
        )]
        public string $idempotencyKey,
    ) {
    }
}