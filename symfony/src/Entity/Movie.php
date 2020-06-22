<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="movies")
 * @ORM\Entity(repositoryClass="App\Repository\MovieRepository")
 */
class Movie extends BaseEntity
{
    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $genre;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $showtimeFrom;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $showtimeTo;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Movie
     */
    public function setName(string $name): Movie
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getGenre(): string
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     * @return Movie
     */
    public function setGenre(string $genre): Movie
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * @return string
     */
    public function getShowtimeFrom(): string
    {
        return $this->showtimeFrom;
    }

    /**
     * @param string $showtimeFrom
     * @return Movie
     */
    public function setShowtimeFrom(string $showtimeFrom): Movie
    {
        $this->showtimeFrom = $showtimeFrom;
        return $this;
    }

    /**
     * @return string
     */
    public function getShowtimeTo(): string
    {
        return $this->showtimeTo;
    }

    /**
     * @param string $showtimeTo
     * @return Movie
     */
    public function setShowtimeTo(string $showtimeTo): Movie
    {
        $this->showtimeTo = $showtimeTo;
        return $this;
    }
}
