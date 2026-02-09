<?php

namespace App\Dto\Response\V1;

use App\Entity\Transfer;
use DateTimeInterface;

final readonly class TransferResponse
{
    public function __construct(
        public string $id,
        public string $fromAccount,
        public string $toAccount,
        public string $amount,
        public string $currency,
        public string $status,
        public string $createdAt,
        public ?string $failureReason,
    ) {}

    public static function fromEntity(Transfer $transfer): self
    {
        return new self(
            id: $transfer->getId(),
            fromAccount:$transfer->getFromAccount()->getId(),
            toAccount:$transfer->getToAccount()->getId(),
            amount:$transfer->getAmount(),
            currency: $transfer->getCurrency(),
            status:$transfer->getStatus()->value,
            createdAt: $transfer->getCreatedAt()->format(\DateTimeInterface::ATOM),
            failureReason:$transfer->getFailureReason(),
        );
    }
}