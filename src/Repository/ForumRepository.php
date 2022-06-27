<?php

namespace App\Repository;

use App\Entity\Clan\Clan;
use App\Entity\Forum\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    public function getForum($slug = '', Clan $clan = null, $nombreParPage = 20, $page = 1)
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('f');

        if ($clan) {
            $query->where('f.clan = :clan')
            ->setParameter('clan', $clan);
        } else {
            $query->where('f.clan is null');
        }

        if (!empty($slug)) {
            $query->andWhere('f.slug = :slug')
            ->setParameter('slug', $slug);
        }

        $query->orderBy('f.ordre', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }
}
