<?php
namespace App\Controller;

use App\DTO\BaseDTO;
use App\DTO\MovieDTO;
use App\Entity\Movie;
use App\Handler\MovieHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Rest\Route("/api")
 */
class MovieController extends FOSRestController
{
    /**
     * @var MovieHandler
     */
    private $movieHandler;

    /**
     * @param MovieHandler $movieHandler
     */
    public function __construct(MovieHandler $movieHandler)
    {
        $this->movieHandler = $movieHandler;
    }

    /**
     * @Rest\Get(path="/movies", name="app.movies.list")
     *
     * @Rest\QueryParam(
     *     name="page",
     *     nullable=true,
     *     requirements="[1-9][0-9]*",
     *     strict=true,
     *     description="page",
     *     default="1"
     * )
     * @Rest\QueryParam(
     *     name="per_page",
     *     nullable=true,
     *     requirements="[1-9][0-9]*",
     *     strict=true,
     *     description="movies per page",
     *     default="3"
     * )
     * @Rest\QueryParam(
     *     name="week",
     *     nullable=true,
     *     requirements="[1-9][0-9]*",
     *     strict=true,
     *     description="week to show",
     *     default="0"
     * )
     * @Rest\QueryParam(
     *     name="q",
     *     nullable=true,
     *     strict=true,
     *     description="Search query"
     * )
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function indexAction(ParamFetcherInterface $paramFetcher): View
    {
        $movies = $this->movieHandler->getPaginated(
            $paramFetcher->get('page'),
            $paramFetcher->get('per_page'),
            $paramFetcher->get('week'),
            $paramFetcher->get('q')
        );

        return View::create($movies, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(path="/movies/{id}", name="app.movies.get", requirements={"id":"\d+"})
     *
     * @param Movie $product
     *
     * @return View
     */
    public function getAction(Movie $product): View
    {
        $productDto = $this->movieHandler->getDto($product);

        return View::create($productDto, Response::HTTP_OK);
    }

    /**
     * @Rest\Post(path="/movies", name="app.movies.create")
     * @ParamConverter("productDTO", converter="fos_rest.request_body")
     *
     * @param BaseDTO|MovieDTO $productDTO
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return View
     *@throws BadRequestHttpException
     *
     */
    public function createAction(MovieDTO $productDTO, ConstraintViolationListInterface $validationErrors): View
    {
        $this->movieHandler->handleValidationErrors($validationErrors);

        $productDTO = $this->movieHandler->create($productDTO);

        return View::create($productDTO, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put(path="/movies/{id}", name="app.movies.update", requirements={"id":"\d+"})
     * @ParamConverter("productDTO", converter="fos_rest.request_body")
     *
     * @param Movie $product
     * @param BaseDTO|MovieDTO $productDTO
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return View
     *@throws BadRequestHttpException
     *
     */
    public function updateAction(
        Movie $product,
        MovieDTO $productDTO,
        ConstraintViolationListInterface $validationErrors
    ): View {
        $this->movieHandler->handleValidationErrors($validationErrors);

        $productDTO = $this->movieHandler->update($product, $productDTO);

        return View::create($productDTO, Response::HTTP_OK);
    }

    /**
     * @Rest\Delete(path="/movies/{id}", name="app.movies.delete", requirements={"id":"\d+"})
     *
     * @param Movie $product
     *
     * @return View
     */
    public function deleteAction(Movie $product): View
    {
        $this->movieHandler->delete($product);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
