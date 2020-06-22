<?php
namespace App\Transformer;

use App\DTO\BaseDTO;
use App\DTO\MovieDTO;
use App\Entity\BaseEntity;
use App\Entity\Movie;

class MovieTransformer extends BaseTransformer
{
    /**
     * @param BaseDTO|Movie $entity
     *
     * @return BaseDTO|MovieDTO
     */
    public function transform(BaseEntity $entity): BaseDTO
    {
        $dto = new MovieDTO();

        $dto->id = $entity->getId();
        $dto->name = $entity->getName();
        $dto->genre = $entity->getGenre();
        $dto->showtimeFrom = $entity->getShowtimeFrom();
        $dto->showtimeTo = $entity->getShowtimeTo();
        $dto->createdAt = $entity->getCreatedAt();
        $dto->updatedAt = $entity->getUpdatedAt();

        return $dto;
    }

    /**
     * @param BaseDTO|MovieDTO $dto
     * @param BaseEntity|Movie|null $entity
     *
     * @return BaseEntity|Movie
     */
    public function reverseTransform(BaseDTO $dto, ?BaseEntity $entity = null): BaseEntity
    {
        $entity = $entity ?: new Movie();

        return $entity
            ->setName($dto->name)
            ->setGenre($dto->genre)
            ->setShowtimeFrom($dto->showtimeFrom)
            ->setShowtimeTo($dto->showtimeTo);
    }
}
