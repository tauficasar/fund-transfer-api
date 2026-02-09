<?php

namespace App\Controller\Api\V1;

use App\Dto\Request\V1\StoreAccountRequest;
use App\Dto\Response\V1\AccountResponse;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/accounts', name: 'api_v1_account_')]
final class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountService $accountService,
    ){
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Not implemented (out of scope)'
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    #[Route('', name: 'store', methods: ['POST'], format: 'json')]
    public function store(#[MapRequestPayload] StoreAccountRequest $request): JsonResponse
    {
        $account = $this->accountService->create($request);

        return $this->json(
            AccountResponse::fromEntity($account), 
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        return $this->json([
            'message' => 'Not implemented (out of scope)'
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(): JsonResponse
    {
        return $this->json([
            'message' => 'Not implemented (out of scope)'
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function destroy(): JsonResponse
    {
        return $this->json([
            'message' => 'Not implemented (out of scope)'
        ], Response::HTTP_NOT_IMPLEMENTED);
    }
}
