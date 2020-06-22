<?php

namespace App\Tests\Controller;

use App\Entity\Movie;
use App\Tests\ApiTestCase;
use App\Tests\Helper\MovieHelper;
use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

class MovieControllerTest extends ApiTestCase
{
    /**
     * @var MovieHelper $movieHelper
     */
    private $movieHelper;

    protected function setUp()
    {
        parent::setUp();

        $this->movieHelper = new MovieHelper($this->entityManager);
    }

    public function testIndexActionSucceeds(): void
    {
        $this->movieHelper->removeAllMovies();
        $this->movieHelper->createMovie("The Witcher 3: Wild Hunt");
        $this->movieHelper->createMovie("The Witcher 2: Assassins of Kings");
        $this->movieHelper->createMovie("The Witcher");
        $this->movieHelper->createMovie("Witcher Arena");
        $this->movieHelper->createMovie("Gwent: The Witcher Card Game");
        $this->movieHelper->createMovie("The Witcher: Battle Arena");
        $this->movieHelper->createMovie("The Witcher: Rise of the White Wolf");

        $response = $this->performRequest('GET', 'app.movies.list', ['page' => 2], [], false);
        $responseContent = json_decode($response->getContent());

        $this->assertEquals(
            [
                'status_code' => Response::HTTP_OK,
                'content' => [
                    'page' => 2,
                    'per_page' => MovieHelper::DEFAULT_PER_PAGE,
                    'page_count' => 3,
                    'total_pages' => 3,
                    'total_count' => 7,
                    'links' => [
                        'self' => $this->router->generate('app.movies.list', ['page' => 2, 'per_page' => 3]),
                        'first' => $this->router->generate('app.movies.list', ['page' => 1, 'per_page' => 3]),
                        'last' => $this->router->generate('app.movies.list', ['page' => 3, 'per_page' => 3]),
                        'next' => $this->router->generate('app.movies.list', ['page' => 3, 'per_page' => 3]),
                        'previous' => $this->router->generate('app.movies.list', ['page' => 1, 'per_page' => 3]),
                    ],
                    'data_count' => 3,
                ]
            ],
            [
                'status_code' => $response->getStatusCode(),
                'content' => [
                    'page' => $responseContent->page,
                    'per_page' => $responseContent->per_page,
                    'page_count' => $responseContent->page_count,
                    'total_pages' => $responseContent->total_pages,
                    'total_count' => $responseContent->total_count,
                    'links' => [
                        'self' => $responseContent->links->self,
                        'first' => $responseContent->links->first,
                        'last' => $responseContent->links->last,
                        'next' => $responseContent->links->next,
                        'previous' => $responseContent->links->previous,
                    ],
                    'data_count' => \count($responseContent->data),
                ]
            ]
        );
    }

    /**
     * @dataProvider dataTestIndexActionFails
     * @param array $data
     */
    public function testIndexActionFails($data): void
    {
        $response = $this->performRequest('GET', 'app.movies.list', $data, [], false);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @return array
     */
    public function dataTestIndexActionFails() : array
    {
        return [
            'case 1: page less than 1' => [
                'data' => [
                    'page' => 0,
                ]
            ],
            'case 2: page not integer' => [
                'data' => [
                    'page' => 'string',
                ]
            ],
            'case 3: per_page less than 1' => [
                'data' => [
                    'per_page' => -2,
                ]
            ],
            'case 4: per_page not integer' => [
                'data' => [
                    'per_page' => 'string',
                ]
            ],
            'case 5: page is out of range' => [
                'data' => [
                    'page' => 123456789,
                ]
            ],
        ];
    }

    public function testGetActionSuccess(): void
    {
        $movie = $this->movieHelper->createMovie();

        $response = $this->performRequest('GET', 'app.movies.get', ['id' => $movie->getId()], [], false);
        $responseContent = json_decode($response->getContent());

        $this->assertEquals(
            [
                'status_code' => Response::HTTP_OK,
                'content' => [
                    'id' => $movie->getId(),
                    'name' => $movie->getName(),
                    'genre' => $movie->getGenre(),
                    'created_at' => 'exists',
                    'updated_at' => 'exists',
                ]
            ],
            [
                'status_code' => $response->getStatusCode(),
                'content' => [
                    'id' => $responseContent->id,
                    'name' => $responseContent->name,
                    'genre' => $responseContent->genre,
                    'created_at' => $responseContent->created_at ? 'exists' : 'is missing',
                    'updated_at' => $responseContent->updated_at ? 'exists' : 'is missing',
                ]
            ]
        );
    }

    public function testGetActionReturnsNotFound(): void
    {
        $this->movieHelper->removeMovie(['id' => 2077]);

        $response = $this->performRequest('GET', 'app.movies.get', ['id' => 2077], [], false);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreateActionSucceeds(): void
    {
        $this->movieHelper->removeMovie();

        $response = $this->performRequest(
            'POST',
            'app.movies.create',
            [],
            [
                'name' => MovieHelper::TEST_MOVIE_NAME,
                'genre' => MovieHelper::TEST_MOVIE_GENRE,
                'showtime_from' => (new DateTime())->format('Y-m-d H:i:s'),
                'showtime_to' => (new DateTime())->add(new DateInterval('PT2H'))->format('Y-m-d H:i:s'),
            ]
        );

        $responseContent = json_decode($response->getContent());
        $movie = $this->entityManager->getRepository(Movie::class)->findOneBy([
            'name' => MovieHelper::TEST_MOVIE_NAME
        ]);

        $this->assertEquals(
            [
                'status_code' => Response::HTTP_CREATED,
                'content' => [
                    'id' => $movie->getId(),
                    'name' => MovieHelper::TEST_MOVIE_NAME,
                    'genre' => MovieHelper::TEST_MOVIE_GENRE,
                    'created_at' => $movie->getCreatedAt()->format(\DateTime::ATOM),
                    'updated_at' => $movie->getUpdatedAt()->format(\DateTime::ATOM),
                ]
            ],
            [
                'status_code' => $response->getStatusCode(),
                'content' => [
                    'id' => $responseContent->id,
                    'name' => $responseContent->name,
                    'genre' => $responseContent->genre,
                    'created_at' => $responseContent->created_at,
                    'updated_at' => $responseContent->updated_at,
                ]
            ]
        );
    }

    /**
     * @dataProvider dataTestCreateActionReturnsBadRequest
     * @param array $data
     */
    public function testCreateActionReturnsBadRequest(array $data): void
    {
        $response = $this->performRequest('POST', 'app.movies.create', [], $data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @return array
     */
    public function dataTestCreateActionReturnsBadRequest() : array
    {
        return [
            'case 1: no parameters' => [
                'data' => [],
            ],
            'case 2: no name' => [
                'data' => [
                    'genre' => MovieHelper::TEST_MOVIE_GENRE,
                ],
            ],
            'case 3: no genre' => [
                'data' => [
                    'name' => MovieHelper::TEST_MOVIE_NAME,
                ],
            ],
            'case 4: too long name' => [
                'data' => [
                    'name' => str_repeat('n', 255),
                    'genre' => MovieHelper::TEST_MOVIE_GENRE,
                ]
            ],
        ];
    }

    public function testCreateActionReturnsUnauthorized(): void
    {
        $response = $this->performRequest('POST', 'app.movies.create', [], [], false);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testUpdateActionSucceeds(): void
    {
        $this->movieHelper->removeMovie(['name' => 'faulty string']);
        $this->movieHelper->removeMovie();
        $movie = $this->movieHelper->createMovie('faulty string', 'Drama');

        $response = $this->performRequest(
            'PUT',
            'app.movies.update',
            [
                'id' => $movie->getId()
            ],
            [
                'name' => MovieHelper::TEST_MOVIE_NAME,
                'genre' => MovieHelper::TEST_MOVIE_GENRE,
                'showtime_from' => (new DateTime())->format('Y-m-d H:i:s'),
                'showtime_to' => (new DateTime())->add(new DateInterval('PT2H'))->format('Y-m-d H:i:s'),
            ]
        );

        $responseContent = json_decode($response->getContent());

        $this->assertEquals(
            [
                'status_code' => Response::HTTP_OK,
                'content' => [
                    'id' => $movie->getId(),
                    'name' => MovieHelper::TEST_MOVIE_NAME,
                    'genre' => MovieHelper::TEST_MOVIE_GENRE,
                    'created_at' => 'exists',
                    'updated_at' => 'exists',
                ]
            ],
            [
                'status_code' => $response->getStatusCode(),
                'content' => [
                    'id' => $responseContent->id,
                    'name' => $responseContent->name,
                    'genre' => $responseContent->genre,
                    'created_at' => $responseContent->created_at ? 'exists' : 'is missing',
                    'updated_at' => $responseContent->updated_at ? 'exists' : 'is missing',
                ]
            ]
        );
    }

    public function testUpdateActionReturnsNotFound(): void
    {
        $this->movieHelper->removeMovie(['id' => 2077]);

        $response = $this->performRequest(
            'PUT',
            'app.movies.update',
            [
                'id' => 2077
            ],
            [
                'name' => MovieHelper::TEST_MOVIE_NAME,
                'genre' => MovieHelper::TEST_MOVIE_GENRE,
            ]
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @dataProvider dataTestUpdateActionReturnsBadRequest
     * @param array $data
     */
    public function testUpdateActionReturnsBadRequest(array $data): void
    {
        $movie = $this->movieHelper->createMovie('faulty name', '99999');

        $response = $this->performRequest('PUT', 'app.movies.update', ['id' => $movie->getId()], $data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @return array
     */
    public function dataTestUpdateActionReturnsBadRequest() : array
    {
        return [
            'case 1: no parameters' => [
                'data' => [],
            ],
            'case 2: no name' => [
                'data' => [
                    'genre' => MovieHelper::TEST_MOVIE_GENRE,
                ],
            ],
            'case 3: no genre' => [
                'data' => [
                    'name' => MovieHelper::TEST_MOVIE_NAME,
                ],
            ],
            'case 4: too long name' => [
                'data' => [
                    'name' => str_repeat('n', 256),
                    'genre' => MovieHelper::TEST_MOVIE_GENRE,
                ]
            ],
        ];
    }

    public function testUpdateActionReturnsUnauthorized(): void
    {
        $response = $this->performRequest('PUT', 'app.movies.update', ['id' => 2077], [], false);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testDeleteActionSucceeds(): void
    {
        $movie = $this->movieHelper->createMovie();

        $response =  $this->performRequest('DELETE', 'app.movies.delete', ['id' => $movie->getId()]);

        $this->assertEquals(
            [
                'status_code' => Response::HTTP_NO_CONTENT,
                'removed' => true,
            ],
            [
                'status_code' => $response->getStatusCode(),
                'removed' => !(bool)$this->entityManager->find(Movie::class, 2077)
            ]
        );
    }

    public function testDeleteActionReturnsNotFound(): void
    {
        $this->movieHelper->removeMovie(['id' => 2077]);

        $response = $this->performRequest('DELETE', 'app.movies.delete', ['id' => 2077]);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteActionReturnsUnauthorized(): void
    {
        $response = $this->performRequest('DELETE', 'app.movies.delete', ['id' => 2077], [], false);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
