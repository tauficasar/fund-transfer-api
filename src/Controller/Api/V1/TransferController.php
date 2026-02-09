<?php

namespace App\Controller\Api\V1;

use App\Dto\Request\V1\StoreTransferRequest;
use App\Dto\Response\V1\TransferResponse;
use App\Service\TransferService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/transfers', name: 'api_v1_transfer_')]
final class TransferController extends AbstractController
{
    public function __construct(
        private readonly TransferService $transferService,
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
    public function store(#[MapRequestPayload] StoreTransferRequest $request): JsonResponse 
    {
        $transfer = $this->transferService->execute($request);

        return $this->json(
            TransferResponse::fromEntity($transfer),
            Response::HTTP_CREATED
        );
    }
}
