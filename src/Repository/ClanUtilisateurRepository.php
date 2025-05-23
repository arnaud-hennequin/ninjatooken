<?php

namespace App\Repository;

use App\Entity\Clan\Clan;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ClanUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClanUtilisateur::class);
    }

    public function getMembres(?Clan $clan = null, $droit = null, ?UserInterface $recruteur = null, $nombreParPage = 100, $page = 1)
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('cu');

        if (isset($clan)) {
            $query->where('cu.clan = :clan')
                ->setParameter('clan', $clan);
        }
        if (isset($droit)) {
            $query->andWhere('cu.droit = :droit')
                ->setParameter('droit', $droit);
        }
        if (isset($recruteur)) {
            $query->andWhere('cu.recruteur = :recruteur')
                ->andWhere('cu.membre <> :recruteur')
                ->setParameter('recruteur', $recruteur);
        }

        $query->orderBy('cu.dateAjout', 'ASC')
            ->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }

    public function getMembreByClanUser(?Clan $clan = null, ?User $user = null)
    {
        $query = $this->createQueryBuilder('cu');

        if (isset($clan)) {
            $query->where('cu.clan = :clan')
                ->setParameter('clan', $clan);
        }
        if (isset($user)) {
            $query->andWhere('cu.membre = :user')
                ->setParameter('user', $user);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function removeByClan(?Clan $clan = null): bool
    {
        if ($clan) {
            $query = $this->createQueryBuilder('cu')
                ->delete('App\Entity\Clan\ClanUtilisateur', 'cu')
                ->where('cu.clan = :clan')
                ->setParameter('clan', $clan)
                ->getQuery();

            return 1 === $query->getSingleScalarResult();
        }

        return false;
    }
}
