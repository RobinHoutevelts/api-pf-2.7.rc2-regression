<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BooksTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private Client $client;
    private Router $router;

    protected function setup(): void
    {
        $this->client = static::createClient();
        $router = static::getContainer()->get('api_platform.router');
        if (!$router instanceof Router) {
            throw new \RuntimeException('api_platform.router service not found.');
        }
        $this->router = $router;
    }

    public function testItSeesABook(): void
    {
        $book = $this->getBook();
        static::assertInstanceOf(Book::class, $book);

        $expectedAttributes = $this->getExpectedAttributes($book);

        $response = $this->doGet('/books/'.$book->getUuid());
        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)['data'] ?? [];
        static::assertEquals($expectedAttributes, $data);
    }

    public function testItCreatesABook(): void
    {
        $response = $this->doPost(
            '/books',
            [
                'title' => 'Api-Platform is awesome',
                'isbn' => '0983769001',
                'author' => 'Santa Claus',
                'description' => 'Great',
                'publicationDate' => '2022-12-25',
            ]
        );
        static::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)['data'] ?? [];
        static::assertArrayHasKey('id', $data, 'The created resource has no resource identifier.');
        static::assertSame('Book', $data['type'], 'The type of the created resource is not a Book.');

        $attributes = $data['attributes'] ?? [];
        static::assertTrue(Uuid::isValid($attributes['id']), 'The id of the generated resource is not an uuid.');

        static::assertEquals('Api-Platform is awesome', $data['attributes']['title']);
    }

    private function doPost(string $path, array $json = []): ResponseInterface
    {
        return $this->doRequest('POST', $path, $json);
    }

    private function doGet(string $path): ResponseInterface
    {
        return $this->doRequest('GET', $path);
    }

    private function doRequest(
        string $method,
        string $path,
        ?array $json = null,
    ): ResponseInterface {
        $options = [
            'json' => $json,
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/vnd.api+json',
            ],
        ];
        if (null === $json) {
            unset($options['json']);
        }

        if ('PATCH' === $method) {
            $options['headers'] = [
                'Content-Type: application/merge-patch+json',
                'Accept: application/vnd.api+json',
            ];
        }

        return $this->client->request(
            $method,
            $path,
            $options,
        );
    }

    /**
     * Get a book with reviews.
     */
    private function getBook(): Book
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $qb = $em->getRepository(Book::class)->createQueryBuilder('b');
        $qb->innerJoin('b.reviews', 'r');
        $qb->setMaxResults(1);

        /** @var Book $book */
        $book = $qb->getQuery()->getSingleResult();

        return $book;
    }

    private function getExpectedAttributes(Book $book): array
    {
        $expectedAttributes = [
            'id' => '/books/'.$book->getUuid(),
            'type' => 'Book',
            'attributes' => [
                'id' => $book->getUuid(),
                'isbn' => $book->isbn,
                'title' => $book->title,
                'description' => $book->description,
                'author' => $book->author,
                'publicationDate' => $book->publicationDate?->format('Y-m-d'),
                'reviews' => [],
            ],
        ];

        foreach ($book->getReviews() as $review) {
            $expectedAttributes['attributes']['reviews'][] = [
                'data' => [
                    'id' => '/reviews/'.$review->getId(),
                    'type' => 'Review',
                    'attributes' => [
                        'id' => (string) $review->getId(),
                        'body' => $review->body,
                    ],
                ],
            ];
        }

        return $expectedAttributes;
    }
}
