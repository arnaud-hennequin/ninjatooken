<?php

namespace App\Repository;

use App\Entity\User\Message;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return array<int, Message>
     */
    public function getSendMessages(UserInterface $user, int $nombreParPage = 5, int $page = 1): array
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('m')
            ->where('m.author = :author')
            ->andWhere('m.hasDeleted = 0')
            ->setParameter('author', $user)
            ->addOrderBy('m.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return array<int, Message>
     */
    public function getFirstSendMessage(UserInterface $user): array
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.author = :author')
            ->andWhere('m.hasDeleted = 0')
            ->setParameter('author', $user)
            ->addGroupBy('m.id')
            ->addOrderBy('m.dateAjout', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getResult();
    }

    public function getNumSendMessages(UserInterface $user): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->where('m.author = :author')
            ->andWhere('m.hasDeleted = 0')
            ->setParameter('author', $user)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @return array<int, Message>
     */
    public function getReceiveMessages(UserInterface $user, int $nombreParPage = 5, int $page = 1): array
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('m')
            ->leftJoin('m.receivers', 'mu')
            ->where('mu.destinataire = :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->addGroupBy('m.id')
            ->addOrderBy('m.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage)
            ->getQuery();

        return $query->getResult();
    }

    public function getFirstReceiveMessage(UserInterface $user): Message
    {
        $query = $this->createQueryBuilder('m')
            ->leftJoin('m.receivers', 'mu')
            ->where('mu.destinataire = :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->addGroupBy('m.id')
            ->addOrderBy('m.dateAjout', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getResult();
    }

    public function getNumReceiveMessages(UserInterface $user): int
    {
        $query = $this->createQueryBuilder('m')
            ->leftJoin('m.receivers', 'mu')
            ->select('COUNT(m)')
            ->where('mu.destinataire = :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getNumNewMessages(UserInterface $user): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->innerJoin('App\Entity\User\MessageUser', 'mu', 'WITH', 'm.id = mu.message')
            ->where('mu.destinataire = :user')
            ->andWhere('mu.dateRead is NULL')
            ->andWhere('mu.hasDeleted = :hasDeleted')
            ->setParameter('user', $user)
            ->setParameter('hasDeleted', false)
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}
