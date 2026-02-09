<?php

namespace App\Entity;

use App\Enum\TransferStatusEnum;
use App\Repository\TransferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TransferRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Transfer
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $fromAccount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $toAccount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(enumType: TransferStatusEnum::class)]
    private ?TransferStatusEnum $status = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $idempotencyKey = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $failureReason = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __construct(
        Account $fromAccount,
        Account $toAccount,
        string $amount,
        string $currency,
        ?string $idempotencyKey = null
    ) {
        $this->fromAccount = $fromAccount;
        $this->toAccount = $toAccount;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->idempotencyKey = $idempotencyKey;
        $this->status = TransferStatusEnum::PENDING;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFromAccount(): ?Account
    {
        return $this->fromAccount;
    }

    public function setFromAccount(?Account $fromAccount): static
    {
        $this->fromAccount = $fromAccount;

        return $this;
    }

    public function getToAccount(): ?Account
    {
        return $this->toAccount;
    }

    public function setToAccount(?Account $toAccount): static
    {
        $this->toAccount = $toAccount;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStatus(): ?TransferStatusEnum
    {
        return $this->status;
    }

    public function setStatus(TransferStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function setIdempotencyKey(string $idempotencyKey): static
    {
        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;

        return $this;
    }

}
