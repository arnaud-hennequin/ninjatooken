<?php

namespace App\Repository;

use App\Entity\Clan\Clan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clan>
 */
class ClanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clan::class);
    }

    /**
     * @return array<int, Clan>
     */
    public function getClans(string $order = 'date', int $nombreParPage = 5, int $page = 1): array
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('c')
            ->addSelect('c')
            ->leftJoin('App\Entity\Clan\ClanUtilisateur', 'cu', 'WITH', 'c.id = cu.clan')
            ->addSelect('COUNT(cu) as num')
            ->where('c.online = :online')
            ->setParameter('online', true)
            ->groupBy('c.id')
            ->distinct(true);

        // par date d'ajout
        if ('date' === $order) {
            $query->addOrderBy('c.dateAjout', 'DESC');
        // par nombre de ninja
        } elseif ('ninja' === $order) {
            $query->addOrderBy('num', 'DESC');
        // par composition
        } elseif ('composition' === $order) {
            $query->leftJoin('App\Entity\Game\Ninja', 'n', 'WITH', 'n.user = cu.membre')
                ->addSelect('AVG(n.experience)*COUNT(cu) as avgxp')
                ->addOrderBy('avgxp', 'DESC');
        // par moyenne d'expérience
        } elseif ('experience' === $order) {
            $query->leftJoin('App\Entity\Game\Ninja', 'n', 'WITH', 'n.user = cu.membre')
                ->addSelect('AVG(n.experience) as avgxp')
                ->addOrderBy('avgxp', 'DESC');
        }

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }

    public function getNumClans(): int
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.online = :online')
            ->setParameter('online', true)
            ->select('COUNT(c)');

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<int, Clan>
     */
    public function searchClans(string $q = '', int $nombreParPage = 5, int $page = 1): array
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.online = :online')
            ->setParameter('online', true)
            ->addOrderBy('c.dateAjout', 'DESC');

        if (!empty($q)) {
            $query->andWhere('c.nom LIKE :q')
                ->andWhere('c.description LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }
}
