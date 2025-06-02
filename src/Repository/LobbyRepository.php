<?php

namespace App\Repository;

use App\Entity\Game\Lobby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lobby>
 */
class LobbyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lobby::class);
    }

    /**
     * @return array<int, Lobby>
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getRecent(int $nombre = 3, int $page = 1): array
    {
        $page = max(1, $page);

        $this->deleteOld();

        $query = $this->createQueryBuilder('a')
            ->where('a.dateUpdate>:date')
            ->setParameter('date', new \DateTime('-10 minutes'))
            ->orderBy('a.dateDebut', 'DESC');

        $query->setFirstResult(($page - 1) * $nombre)
            ->setMaxResults($nombre);

        return $query->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function deleteOld(): bool
    {
        $query = $this->createQueryBuilder('a')
            ->delete('App\Entity\Game\Lobby', 'a')
            ->where('a.dateUpdate<=:date')
            ->setParameter('date', new \DateTime('-10 minutes'))
            ->getQuery();

        return 1 === $query->getSingleScalarResult();
    }
}
