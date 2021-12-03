<?php
namespace App\Repository;

use App\Entity\Clan\ClanPostulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Clan\Clan;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class ClanPostulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClanPostulation::class);
    }

    public function getByClanUser(?Clan $clan = null, ?UserInterface $user = null)
    {
        $query = $this->createQueryBuilder('cp');

        if(isset($clan)){
            $query->where('cp.clan = :clan')
                ->setParameter('clan', $clan);
        }
        if(isset($user)){
            $query->andWhere('cp.postulant = :user')
                ->setParameter('user', $user);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getByUser(?UserInterface $user = null)
    {

        if(isset($user)){
            $query = $this->createQueryBuilder('cp');
            $query->andWhere('cp.postulant = :user')
                ->setParameter('user', $user);
            return $query->getQuery()->getResult();
        }
        return null;
    }

    public function getByClan(?Clan $clan = null)
    {

        if(isset($clan)){
            $query = $this->createQueryBuilder('cp');
            $query->where('cp.clan = :clan')
                ->setParameter('clan', $clan);

            return $query->getQuery()->getResult();
        }
        return null;
    }
}