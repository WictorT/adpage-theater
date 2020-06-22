<?php

namespace App\Tests\Helper;

use App\Entity\Movie;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use const DATE_ATOM;

class MovieHelper
{
    const DEFAULT_PER_PAGE = 3;
    const DEFAULT_PAGE = 1;
    const TEST_MOVIE_NAME = 'Cyberpunk 2077';
    const TEST_MOVIE_GENRE = 'Action';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name
     * @param float $genre
     * @return Movie|null|object
     */
    public function createMovie($name = self::TEST_MOVIE_NAME, $genre = self::TEST_MOVIE_GENRE): Movie
    {
        $movie = $this->entityManager->getRepository(Movie::class)->findOneBy(['name' => $name]);

        if ($movie) {
            $movie->setGenre($genre);
            $this->entityManager->merge($movie);
        } else {
            $movie = (new Movie)
                ->setName($name)
                ->setGenre($genre)
                ->setShowtimeFrom((new DateTime())->format(DATE_ATOM))
                ->setShowtimeTo((new DateTime())->add(new DateInterval('PT2H'))->format(DATE_ATOM));

            $this->entityManager->persist($movie);
        }
        $this->entityManager->flush();

        return $movie;
    }

    /**
     * @param array $findParams
     * @return void
     */
    public function removeMovie(array $findParams = ['name' => self::TEST_MOVIE_NAME]): void
    {
        $movie = $this->entityManager->getRepository(Movie::class)->findOneBy($findParams);
        $movie && $this->entityManager->remove($movie);

        $this->entityManager->flush();
    }

    public function removeAllMovies(): void
    {
        $movies = $this->entityManager->getRepository(Movie::class)->findAll();

        foreach ($movies as $movie) {
            $movie && $this->entityManager->remove($movie);
        }

        $this->entityManager->flush();
    }
}
