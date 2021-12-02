<?php
namespace App\Repository;

use App\Entity\User\MessageUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\User\User;
use Doctrine\Persistence\ManagerRegistry;

class MessageUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageUser::class);
    }

    public function getReceiveMessages(User $user, $nombreParPage=5, $page=1)
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
            ->setFirstResult(($page-1) * $nombreParPage)
            ->setMaxResults($nombreParPage)
            ->getQuery();

        return $query->getResult();
    }

    public function getFirstReceiveMessage(User $user)
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

    public function getNumReceiveMessages(User $user)
    {
        $query = $this->createQueryBuilder('mu')
            ->leftJoin('mu.message', 'm')
            ->select('COUNT(m)')
            ->where('mu.destinataire = :user')
            ->andWhere('m.author <> :user')
            ->andWhere('mu.hasDeleted = 0')
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}