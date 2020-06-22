<?php
namespace App\Handler;

use App\DTO\BaseDTO;
use App\DTO\MovieDTO;
use App\Entity\BaseEntity;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Transformer\MovieTransformer;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MovieHandler extends BaseHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MovieTransformer
     */
    private $transformer;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param MovieTransformer $transformer
     * @param UrlGeneratorInterface $router
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MovieTransformer $transformer,
        UrlGeneratorInterface $router,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->transformer = $transformer;
        $this->router = $router;
        $this->validator = $validator;
    }

    /**
     * @param int $productId
     *
     * @return Movie|null
     *@throws NotFoundHttpException
     *
     */
    public function getById(int $productId): ?Movie
    {
        /** @var Movie $product */
        $product = $this->getRepository()->find($productId);
        if ($product === null) {
            throw new NotFoundHttpException();
        }

        return $product;
    }

    /**
     * @param BaseEntity|Movie $product
     *
     * @return BaseDTO|MovieDTO
     */
    public function getDto(BaseEntity $product): BaseDTO
    {
        return $this->transformer->transform($product);
    }

    /**
     * @param int $page
     * @param int $perPage
     *
     * @param int $weekNumber
     * @param string|null $q
     * @return array
     */
    public function getPaginated(int $page, int $perPage, int $weekNumber, ?string $q): array
    {
        $queryBuilder = $this->getRepository()
            ->createQueryBuilder('p')
            ->andWhere('p.showtimeFrom BETWEEN :from AND :to')
            ->setParameter('from', (new DateTime)->add(new DateInterval("P{$weekNumber}W")))
            ->setParameter('to', (new DateTime)->modify('Monday next week')->add(new DateInterval("P{$weekNumber}W")));

        if ($q) {
            $queryBuilder = $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('lower(p.name)', ':q'),
                    $queryBuilder->expr()->eq('lower(p.genre)', ':q')
                ))
                ->setParameter('q', strtolower($q));
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);

        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage($perPage);
        try {
            $paginator->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $pageResults = $paginator->getCurrentPageResults();

        return [
            'page' => $paginator->getCurrentPage(),
            'per_page' => $paginator->getMaxPerPage(),
            'page_count' => \count($pageResults),
            'total_pages' => $paginator->getNbPages(),
            'total_count' => $paginator->getNbResults(),
            'links' => $this->getPaginationLinks($paginator),
            'data' => $this->transformer->transformMultiple($pageResults)
        ];
    }

    /**
     * @param BaseDTO|MovieDTO $productDto
     *
     * @return BaseDTO
     */
    public function create(BaseDTO $productDto): BaseDTO
    {
        $product = $this->transformer->reverseTransform($productDto);

        $validationErrors = $this->validator->validate($product);
        $this->handleValidationErrors($validationErrors);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $this->entityManager->refresh($product);

        return $this->transformer->transform($product);
    }

    /**
     * @param BaseEntity $product
     * @param BaseDTO $productDto
     *
     * @return BaseDTO
     */
    public function update(BaseEntity $product, BaseDTO $productDto): BaseDTO
    {
        $product = $this->transformer->reverseTransform($productDto, $product);

        $validationErrors = $this->validator->validate($product);
        $this->handleValidationErrors($validationErrors);

        $this->entityManager->merge($product);
        $this->entityManager->flush();
        $this->entityManager->refresh($product);

        return $this->transformer->transform($product);
    }

    /**
     * @param BaseEntity|Movie $product
     *
     * @return void
     */
    public function delete(BaseEntity $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    /**
     * @return MovieRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(Movie::class);
    }

    /**
     * @param Pagerfanta $paginator
     * @return array
     */
    private function getPaginationLinks(Pagerfanta $paginator): array
    {
        $links = [];

        $links['self'] = $this->router->generate(
            'app.movies.list',
            [
                'page' => $paginator->getCurrentPage(),
                'per_page' => $paginator->getMaxPerPage()
            ]
        );

        $links['first'] = $this->router->generate(
            'app.movies.list',
            [
                'page' => 1,
                'per_page' => $paginator->getMaxPerPage()
            ]
        );

        $links['last'] = $this->router->generate(
            'app.movies.list',
            [
                'page' => $paginator->getNbPages(),
                'per_page' => $paginator->getMaxPerPage()
            ]
        );

        $paginator->hasPreviousPage() && $links['previous'] = $this->router->generate(
            'app.movies.list',
            [
                'page' => $paginator->getCurrentPage() - 1,
                'per_page' => $paginator->getMaxPerPage()
            ]
        );

        $paginator->hasNextPage() && $links['next'] = $this->router->generate(
            'app.movies.list',
            [
                'page' => $paginator->getCurrentPage() + 1,
                'per_page' => $paginator->getMaxPerPage()
            ]
        );

        return $links;
    }
}
