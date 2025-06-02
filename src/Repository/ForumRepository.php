<?php

namespace App\Repository;

use App\Entity\Clan\Clan;
use App\Entity\Forum\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Forum>
 */
class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    /**
     * @return array<int, Forum>
     */
    public function getForum(
        string $slug = '',
        ?Clan $clan = null,
        int $nombreParPage = 20,
        int $page = 1,
    ): array {
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
