<?php

namespace App\Repository;

use App\Entity\User\MessageUser;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageUser>
 */
class MessageUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageUser::class);
    }

    /**
     * @return array<int, MessageUser>
     */
    public function getReceiveMessages(User $user, int $nombreParPage = 5, int $page = 1): array
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('mu')
            ->leftJoin('mu.message', 'm')
            ->where('mu.destinataire = :user')
            ->andWhere('m.author <> :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->addGroupBy('m.id')
            ->addOrderBy('m.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return array<int, MessageUser>
     */
    public function getFirstReceiveMessage(User $user): array
    {
        $query = $this->createQueryBuilder('mu')
            ->leftJoin('mu.message', 'm')
            ->where('mu.destinataire = :user')
            ->andWhere('m.author <> :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->addGroupBy('m.id')
            ->addOrderBy('m.dateAjout', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNumReceiveMessages(User $user): int
    {
        $query = $this->createQueryBuilder('mu')
            ->leftJoin('mu.message', 'm')
            ->select('COUNT(m)')
            ->where('mu.destinataire = :user')
            ->andWhere('m.author <> :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }
}
