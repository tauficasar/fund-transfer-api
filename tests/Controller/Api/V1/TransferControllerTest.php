<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class TransferControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/transfers');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_IMPLEMENTED);
    }

    public function testTransferSuccess(): void
    {
        $client = static::createClient();

        /**
         * Create two accounts first
         */
        $from = $this->createAccount($client, '1000.00');
        $to   = $this->createAccount($client, '100.00');

        $payload = [
            'fromAccountId' => $from['id'],
            'toAccountId'   => $to['id'],
            'currency'      => 'INR',
            'amount'        => '200.00',
            'idempotencyKey'=> 'test-transfer-123',
        ];

        $client->request(
            'POST',
            '/api/v1/transfers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $response);
        $this->assertSame('completed', $response['status']);
        $this->assertSame('200.00', $response['amount']);
        $this->assertSame('INR', $response['currency']);
    }

    public function testTransferValidationFailure(): void
    {
        $client = static::createClient();

        $payload = [
            'fromAccountId' => 'invalid',
            'toAccountId'   => 'invalid',
            'currency'      => 'INR',
            'amount'        => '10',
            'idempotencyKey'=> 'a724790e78a0',
        ];

        $client->request(
            'POST',
            '/api/v1/transfers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('fromAccountId', $response['errors']);
        $this->assertArrayHasKey('toAccountId', $response['errors']);
    }

    public function testTransferInsufficientBalance(): void
    {
        $client = static::createClient();

        $from = $this->createAccount($client, '50.00');
        $to   = $this->createAccount($client, '10.00');

        $payload = [
            'fromAccountId' => $from['id'],
            'toAccountId'   => $to['id'],
            'currency'      => 'INR',
            'amount'        => '200.00',
            'idempotencyKey'=> 'a724790e78a0',
        ];

        $client->request(
            'POST',
            '/api/v1/transfers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('INSUFFICIENT_BALANCE', $response['error']['code']);
    }

    public function testIdempotentTransferReplay(): void
    {
        $client = static::createClient();

        $from = $this->createAccount($client, '1000.00');
        $to   = $this->createAccount($client, '100.00');

        $payload = [
            'fromAccountId' => $from['id'],
            'toAccountId'   => $to['id'],
            'currency'      => 'INR',
            'amount'        => '100.00',
            'idempotencyKey'=> 'idem-key-1',
        ];

        // First request
        $client->request(
            'POST',
            '/api/v1/transfers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $first = json_decode($client->getResponse()->getContent(), true);

        // Replay request
        $client->request(
            'POST',
            '/api/v1/transfers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $second = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($first['id'], $second['id']);
    }

    /**
     * Helper to create account
     */
    private function createAccount($client, string $balance): array
    {
        $client->request(
            'POST',
            '/api/v1/accounts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'balance'  => $balance,
                'currency' => 'INR',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return json_decode($client->getResponse()->getContent(), true);
    }
}
