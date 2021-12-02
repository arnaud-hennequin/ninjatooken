<?php
namespace App\Repository;

use App\Entity\User\Capture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\User\User;
use Doctrine\Persistence\ManagerRegistry;

class CaptureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Capture::class);
    }

    public function getCaptures(User $user, $nombreParPage=5, $page=1): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->addOrderBy('c.dateAjout', 'DESC')
            ->setFirstResult(($page-1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return new Paginator($query);
    }
}