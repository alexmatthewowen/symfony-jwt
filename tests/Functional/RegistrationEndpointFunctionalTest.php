<?php

declare(strict_types=1);

namespace Functional;

use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationEndpointFunctionalTest extends WebTestCase
{
    private const URI = '/register';
    private const METHOD = 'POST';

    private readonly KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = self::createClient(server: [
            'CONTENT_TYPE' => 'application/json'
        ]);
    }

    protected function tearDown(): void
    {
        $this->getContainer()
            ->get(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class)
            ->getQuery()
            ->execute()
        ;
    }

    public static function invalidRequestPayloadProvider(): \Generator
    {
        yield 'with email missing' => [
            'payload' => [
                'password' => 'RandomPassword12?'
            ],
            'expectedErrors' => [
                'email' => 'This value should not be blank.'
            ]
        ];

        yield 'with email empty' => [
            'payload' => [
                'email' => '',
                'password' => 'RandomPassword12?'
            ],
            'expectedErrors' => [
                'email' => 'This value should not be blank.'
            ]
        ];

        yield 'with email too long' => [
            'payload' => [
                'email' => 'a@'.\str_repeat('t', 250).'.com',
                'password' => 'RandomPassword12?'
            ],
            'expectedErrors' => [
                'email' => 'This value is too long. It should have 120 characters or less.'
            ]
        ];

        yield 'with email invalid' => [
            'payload' => [
                'email' => 'alexowen123@te',
                'password' => 'RandomPassword12?'
            ],
            'expectedErrors' => [
                'email' => 'This value is not a valid email address.'
            ]
        ];

        yield 'with email null' => [
            'payload' => [
                'email' => null,
                'password' => 'RandomPassword12?'
            ],
            'expectedErrors' => [
                'email' => 'This value should not be blank.'
            ]
        ];

        yield 'with email not a string' => [
            'payload' => [
                'email' => 123,
                'password' => 'RandomPassword12?'
            ],
            'expectedErrors' => [
                'email' => 'This value is not a valid email address.'
            ]
        ];

        yield 'with password missing' => [
            'payload' => [
                'email' => 'alexowen123@test.com',
            ],
            'expectedErrors' => [
                'password' => 'This value should not be blank.'
            ]
        ];

        yield 'with password too short' => [
            'payload' => [
                'email' => 'alexowen123@test.com',
                'password' => 'Randm1!'
            ],
            'expectedErrors' => [
                'password' => 'This value is too short. It should have 8 characters or more.'
            ]
        ];

        yield 'with password too long' => [
            'payload' => [
                'email' => 'alexowen123@test.com',
                'password' => \str_repeat('p', 256)
            ],
            'expectedErrors' => [
                'password' => 'This value is too long. It should have 120 characters or less.'
            ]
        ];

        yield 'with password null' => [
            'payload' => [
                'email' => 'alexowen123@test.com',
                'password' => null
            ],
            'expectedErrors' => [
                'password' => 'This value should not be blank.'
            ]
        ];
    }

    /**
     * @dataProvider invalidRequestPayloadProvider
     */
    public function testUnprocessableEntityResponseOnInvalidRequestPayload(array $payload, array $expectedErrors): void
    {
        $this->client->request(method: self::METHOD, uri: self::URI, content: \json_encode($payload));

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString(
            \json_encode([
                'code' => '422',
                'message' => 'Parameter issues',
                'errors' => $expectedErrors
            ]),
            $this->client->getResponse()->getContent()
        );
    }

    public function testSuccessfulRequest(): void
    {
        $this->client->request(method: self::METHOD, uri: self::URI, content: \json_encode([
            'email' => 'alexmatthewowen1994@gmail.com',
            'password' => 'ThisPasswordSHouldProbablyBeMoreSecure12!'
        ]));
        $this->assertResponseStatusCodeSame(201);

        $json = \json_decode($this->client->getResponse()->getContent(), true, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('token', $json);
    }
}