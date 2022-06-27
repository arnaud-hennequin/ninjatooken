<?php

namespace App\Repository;

use App\Entity\Game\Lobby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LobbyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lobby::class);
    }

    public function getRecent($nombre = 3, $page = 1)
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
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
