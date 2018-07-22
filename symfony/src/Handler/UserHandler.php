<?php
namespace App\Handler;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Repository\BaseRepository;
use App\Repository\UserRepository;
use App\Transformer\UserBaseTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserHandler extends BaseHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserBaseTransformer
     */
    private $transformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserBaseTransformer $transformer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserBaseTransformer $transformer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    /**
     * @param UserDTO $userDTO
     * @return User
     */
    public function create(UserDTO $userDTO): User
    {
        $user = $this->transformer->reverseTransform($userDTO);

        $validationErrors = $this->validator->validate($user);
        $this->handleValidationErrors($validationErrors);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return UserRepository
     */
    public function getRepository(): BaseRepository
    {
        return $this->entityManager->getRepository(User::class);
    }
}
