<?php

namespace App\Service;

use App\Dto\Request\V1\StoreAccountRequest;
use App\Entity\Account;
use App\Repository\AccountRepository;

final class AccountService
{
    public function __construct(
        private readonly AccountRepository $accountRepository
    ) {}

    public function create(StoreAccountRequest $request): Account
    {
        $account = new Account();
        $account->setBalance($request->balance);
        $account->setCurrency($request->currency);

        $this->accountRepository->save($account, true);

        return $account;
    }
}
