<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AccountControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/accounts');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_IMPLEMENTED);
    }

    public function testStoreAccountSuccess(): void
    {
        $client = static::createClient();

        $payload = [
            'balance'  => '1000.50',
            'currency' => 'INR',
        ];

        $client->request(
            'POST',
            '/api/v1/accounts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        // ✅ Status code
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // ✅ JSON structure
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $response);
        $this->assertSame('1000.50', $response['balance']);
        $this->assertSame('INR', $response['currency']);
        $this->assertArrayHasKey('createdAt', $response);
    }

    public function testStoreAccountValidationFailure(): void
    {
        $client = static::createClient();

        $payload = [
            'balance'  => '2000000000000000q.00',
            'currency' => 'INR',
        ];

        $client->request(
            'POST',
            '/api/v1/accounts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('balance', $response['errors']);
        $this->assertSame(
            'balance must be a numeric value',
            $response['errors']['balance'][0]
        );
    }
}
