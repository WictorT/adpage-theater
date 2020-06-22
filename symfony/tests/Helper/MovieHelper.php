<?php

namespace App\Tests\Helper;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;

class MovieHelper
{
    const DEFAULT_PER_PAGE = 3;
    const DEFAULT_PAGE = 1;
    const TEST_PRODUCT_PRICE = 59.99;
    const TEST_PRODUCT_NAME = 'Cyberpunk 2077';

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
     * @param float $price
     * @return Movie|null|object
     */
    public function createProduct($name = self::TEST_PRODUCT_NAME, $price = self::TEST_PRODUCT_PRICE): Movie
    {
        $product = $this->entityManager->getRepository(Movie::class)->findOneBy(['name' => $name]);

        if ($product) {
            $product->setPrice($price);
            $this->entityManager->merge($product);
        } else {
            $product = (new Movie)
                ->setName($name)
                ->setPrice($price);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();

        return $product;
    }

    /**
     * @param array $findParams
     * @return void
     */
    public function removeProduct(array $findParams = ['name' => self::TEST_PRODUCT_NAME]): void
    {
        $product = $this->entityManager->getRepository(Movie::class)->findOneBy($findParams);
        $product && $this->entityManager->remove($product);

        $this->entityManager->flush();
    }

    public function removeAllProducts(): void
    {
        $products = $this->entityManager->getRepository(Movie::class)->findAll();

        foreach ($products as $product) {
            $product && $this->entityManager->remove($product);
        }

        $this->entityManager->flush();
    }
}
