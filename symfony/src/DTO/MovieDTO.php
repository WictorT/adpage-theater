<?php
namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\AccessorOrder("custom", custom = {"id", "name", "genre", "showtime_from", "showtime_to", "created_at", "updated_at"})
 */
class MovieDTO extends BaseDTO
{
    /**
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(max=254)
     *
     * @var string
     */
    public $name;

    /**
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(max=254)
     *
     * @var string
     */
    public $genre;

    /**
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @Assert\Type("string")
     * @var \DateTime
     */
    public $showtimeFrom;

    /**
     * @Serializer\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\DateTime()
     * @Assert\Type("string")
     * @var \DateTime
     */
    public $showtimeTo;
}
