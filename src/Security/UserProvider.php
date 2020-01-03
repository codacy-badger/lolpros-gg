<?php

namespace App\Security;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByUsername($username): ?UserInterface
    {
        return $this->entityManager->createQueryBuilder()
            ->select('users')
            ->where('users.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of %s are not supported.', get_class($user)));
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
