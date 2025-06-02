<?php

namespace App\Repository;

use App\Entity\User\Capture;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Capture>
 */
class CaptureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Capture::class);
    }

    /**
     * @return Paginator<Capture>
     */
    public function getCaptures(UserInterface $user, int $nombreParPage = 5, int $page = 1): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->addOrderBy('c.dateAjout', 'DESC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return new Paginator($query);
    }
}
