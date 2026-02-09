<?php

namespace App\Service;

use App\Dto\Request\V1\StoreTransferRequest;
use App\Entity\Account;
use App\Entity\Transfer;
use App\Enum\TransferStatusEnum;
use App\Exception\AccountNotFoundException;
use App\Exception\InsufficientBalanceException;
use App\Exception\InvalidTransferException;
use App\Repository\AccountRepository;
use App\Repository\TransferRepository;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

final class TransferService
{
    private const IDEMPOTENCY_TTL = 86400; // 24 hours

    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly TransferRepository $transferRepository,
        private readonly CacheItemPoolInterface $idempotencyStore,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(StoreTransferRequest $request): Transfer
    {
        if ($request->fromAccountId === $request->toAccountId) {
            throw InvalidTransferException::sameAccount();
        }

        $idempotencyKey = $request->idempotencyKey;
        if ($idempotencyKey !== null) {
            $existing = $this->resolveIdempotentTransfer($idempotencyKey);
            if ($existing !== null) {
                $this->logger->info('Idempotent transfer replay', [
                    'idempotency_key' => $idempotencyKey,
                    'transfer_id' => $existing->getId(),
                ]);

                return $existing;
            }
            // Also check DB in case cache was lost (e.g. between requests in same process)
            $existingInDb = $this->transferRepository->findByIdempotencyKey($idempotencyKey);
            if ($existingInDb !== null) {
                $this->storeIdempotencyKey($idempotencyKey, $existingInDb->getId());

                return $existingInDb;
            }
        }

        $fromAccount = $this->accountRepository->findById($request->fromAccountId);
        $toAccount = $this->accountRepository->findById($request->toAccountId);

        if ($fromAccount === null) {
            throw AccountNotFoundException::withId($request->fromAccountId);
        }
        if ($toAccount === null) {
            throw AccountNotFoundException::withId($request->toAccountId);
        }

        if ($fromAccount->getCurrency() !== $request->currency || $toAccount->getCurrency() !== $request->currency) {
            throw InvalidTransferException::currencyMismatch();
        }

        $amount = $request->amount;
        $transfer = new Transfer($fromAccount, $toAccount, $amount, $request->currency, $idempotencyKey);

        try {
            $this->transferRepository->getEntityManager()->wrapInTransaction(function () use ($fromAccount, $toAccount, $amount, $transfer): void {
                $this->debitAndCredit($fromAccount, $toAccount, $amount);
                $transfer->setStatus(TransferStatusEnum::COMPLETED);
                $this->transferRepository->getEntityManager()->persist($transfer);
                $this->transferRepository->getEntityManager()->flush();
            });
        } catch (InsufficientBalanceException $e) {
            $transfer->setStatus(TransferStatusEnum::FAILED);
            $transfer->setFailureReason($e->getMessage());
            $this->persistFailedTransferIfOpen($transfer);
            $this->logger->warning('Transfer failed: insufficient balance', [
                'from_account' => $fromAccount->getId(),
                'amount' => $amount,
            ]);
            throw $e;
        } catch (\Throwable $e) {
            $transfer->setStatus(TransferStatusEnum::FAILED);
            $transfer->setFailureReason($e->getMessage());
            $this->persistFailedTransferIfOpen($transfer);
            $this->logger->error('Transfer failed', [
                'from_account' => $fromAccount->getId(),
                'to_account' => $toAccount->getId(),
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw InvalidTransferException::transferFailed();
        }

        if ($idempotencyKey !== null) {
            $this->storeIdempotencyKey($idempotencyKey, $transfer->getId());
        }

        $this->logger->info('Transfer completed', [
            'transfer_id' => $transfer->getId(),
            'from_account' => $fromAccount->getId(),
            'to_account' => $toAccount->getId(),
            'amount' => $amount,
        ]);

        return $transfer;
    }

    public function getTransferById(string $id): Transfer
    {
        $transfer = $this->transferRepository->findById($id);
        if ($transfer === null) {
            throw InvalidTransferException::transferNotFound($id);
        }

        return $transfer;
    }

    public function getAccountById(string $id): Account
    {
        $account = $this->accountRepository->findById($id);
        if ($account === null) {
            throw AccountNotFoundException::withId($id);
        }

        return $account;
    }

    private function resolveIdempotentTransfer(string $idempotencyKey): ?Transfer
    {
        $cached = $this->idempotencyStore->getItem($this->idempotencyCacheKey($idempotencyKey));
     
        if (!$cached->isHit()) {
            return null;
        }
        $transferId = $cached->get();
        if (!\is_string($transferId)) {
            return null;
        }

        return $this->transferRepository->findById($transferId);
    }

    private function storeIdempotencyKey(string $idempotencyKey, string $transferId): void
    {
        $item = $this->idempotencyStore->getItem($this->idempotencyCacheKey($idempotencyKey));
        $item->set($transferId);
        $item->expiresAfter(self::IDEMPOTENCY_TTL);
        $this->idempotencyStore->save($item);
    }

    private function idempotencyCacheKey(string $key): string
    {
        return 'transfer_idempotency_' . hash('xxh128', $key);
    }

    private function persistFailedTransferIfOpen(Transfer $transfer): void
    {
        $em = $this->transferRepository->getEntityManager();
        if ($em->isOpen()) {
            $em->persist($transfer);
            $em->flush();
        }
    }

    private function debitAndCredit(Account $fromAccount, Account $toAccount, string $amount): void
    {
        $em = $this->transferRepository->getEntityManager();

        $from = $this->accountRepository->findByIdForUpdate($fromAccount->getId());
        $to = $this->accountRepository->findByIdForUpdate($toAccount->getId());
        
        if ($from === null || $to === null) {
            throw InvalidTransferException::accountUnavailable();
        }
        
        $fromBalance = $from->getBalance();
        if (bccomp($fromBalance, $amount, 2) < 0) {
            throw new InsufficientBalanceException($fromBalance, $amount);
        }
        
        $from->setBalance(bcsub($fromBalance, $amount, 2));
        $to->setBalance(bcadd($to->getBalance(), $amount, 2));
        $em->persist($from);
        $em->persist($to);
    }
}
