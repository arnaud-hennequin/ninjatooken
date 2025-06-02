<?php

namespace App\Repository;

use App\Entity\User\Friend;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friend>
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    /**
     * @return array<int, Friend>
     */
    public function getFriends(UserInterface $user, int $nombreParPage = 5, int $page = 1): array
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.isConfirmed = true')
            ->andWhere('f.isBlocked = false')
            ->setParameter('user', $user)
            ->addOrderBy('f.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage)
            ->distinct(true);

        return $query->getQuery()->getResult();
    }

    /**
     * @return Paginator<Friend>
     */
    public function getDemandes(UserInterface $user, int $nombreParPage = 5, int $page = 1): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.isConfirmed = false')
            ->andWhere('f.isBlocked = false')
            ->setParameter('user', $user)
            ->addOrderBy('f.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return new Paginator($query);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNumFriends(UserInterface $user): int
    {
        $query = $this->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.user = :user')
            ->andWhere('f.isConfirmed = true')
            ->andWhere('f.isBlocked = false')
            ->setParameter('user', $user)
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNumDemandes(UserInterface $user): int
    {
        $query = $this->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.user = :user')
            ->andWhere('f.isConfirmed = false')
            ->andWhere('f.isBlocked = false')
            ->setParameter('user', $user)
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNumBlocked(UserInterface $user): int
    {
        $query = $this->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->where('f.user = :user')
            ->andWhere('f.isBlocked = true')
            ->setParameter('user', $user)
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    /**
     * @return Paginator<Friend>
     */
    public function getBlocked(UserInterface $user, int $nombreParPage = 5, int $page = 1): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.isBlocked = true')
            ->setParameter('user', $user)
            ->addOrderBy('f.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return new Paginator($query);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function deleteAllBlocked(?UserInterface $user = null): bool
    {
        if ($user) {
            $query = $this->createQueryBuilder('f')
                ->delete('App\Entity\User\Friend', 'f')
                ->where('f.user = :user')
                ->andWhere('f.isBlocked = true')
                ->setParameter('user', $user)
                ->getQuery();

            return 1 === $query->getSingleScalarResult();
        }

        return false;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function deleteAllDemandes(?UserInterface $user = null): bool
    {
        if ($user) {
            $query = $this->createQueryBuilder('f')
                ->delete('App\Entity\User\Friend', 'f')
                ->where('f.user = :user')
                ->andWhere('f.isConfirmed = false')
                ->setParameter('user', $user)
                ->getQuery();

            return 1 === $query->getSingleScalarResult();
        }

        return false;
    }
}
