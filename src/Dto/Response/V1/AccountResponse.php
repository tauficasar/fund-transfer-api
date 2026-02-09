<?php

namespace App\Dto\Response\V1;

use App\Entity\Account;
use DateTimeInterface;

final readonly class AccountResponse
{
    public function __construct(
        public string $id,
        public string $currency,
        public string $balance,
        public string $createdAt,
    ) {}

    public static function fromEntity(Account $account): self
    {
        return new self(
            id: $account->getId(),
            currency: $account->getCurrency(),
            balance: $account->getBalance(),
            createdAt: $account->getCreatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}