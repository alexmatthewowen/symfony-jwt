<?php

declare(strict_types=1);

namespace Functional;

use App\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class LoginEndpointFunctionalTest extends WebTestCase
{
    private const URI = '/login';
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
            ->createQueryBuilder('u')
            ->delete(User::class)
            ->getQuery()
            ->execute()
        ;
    }

    public static function invalidPayloadProvider(): \Generator
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

        yield 'with password null' => [
            'payload' => [
                'email' => 'alexowen123@test.com',
                'password' => null
            ],
            'expectedErrors' => [
                'password' => 'This value should not be blank.'
            ]
        ];

        yield 'with password not string' => [
            'payload' => [
                'email' => 'alexowen123@test.com',
                'password' => 12345678
            ],
            'expectedErrors' => [
                'password' => 'This value should be of type string.'
            ]
        ];
    }

    /**
     * @dataProvider invalidPayloadProvider
     */
    public function testUnprocessableEntityResponseOnInvalidPayload(array $payload, array $expectedErrors): void
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


    public function testInvalidLoginCredentials(): void
    {
        $this->client->request(method: self::METHOD, uri: self::URI, content: \json_encode([
            'email' => 'alex.owen@testemail.com',
            'password' => 'Spersecurepassword12!'
        ]));

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonStringEqualsJsonString(
            \json_encode([
                'code' => '401',
                'message' => 'Invalid login credentials.'
            ]),
            $this->client->getResponse()->getContent()
        );
    }

    public function testSuccessfulLogin(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        /** @var PasswordHasherInterface $hasher */
        $hasher = $this->getContainer()->get(PasswordHasherInterface::class);
        $user = new User(
            Uuid::uuid4(),
            'alex.owen@testemail.com',
            $hasher->hash('Spersecurepassword12!')
        );
        $em->persist($user);
        $em->flush();

        $this->client->request(method: self::METHOD, uri: self::URI, content: \json_encode([
            'email' => 'alex.owen@testemail.com',
            'password' => 'Spersecurepassword12!'
        ]));

        $this->assertResponseStatusCodeSame(200);

        $json = \json_decode($this->client->getResponse()->getContent(), true, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('token', $json);
    }
}