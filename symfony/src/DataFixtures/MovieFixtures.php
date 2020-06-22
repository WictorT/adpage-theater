<?php
namespace App\DataFixtures;

use App\Entity\Movie;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    const MOVIES = [
        [
            'name' => 'Fallout',
            'genre' => 'Drama',
        ],
        [
            'name' => 'Don’t Starve',
            'genre' => 'Drama',
        ],
        [
            'name' => 'Baldur’s Gate',
            'genre' => 'Action',
        ],
        [
            'name' => 'Icewind Dale',
            'genre' => 'Action',
        ],
        [
            'name' => 'Bloodborne',
            'genre' => 'Scary',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::MOVIES as $key => $movie) {
            $showtimeInDays = $key * 3;

            $user = (new Movie)
                ->setName($movie['name'])
                ->setGenre($movie['genre'])
                ->setShowtimeFrom((new DateTime)->add(new DateInterval("P{$showtimeInDays}D")))
                ->setShowtimeTo((new DateTime)->add(new DateInterval("P{$showtimeInDays}DT2H")));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
