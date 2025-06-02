<?php

namespace App\Repository;

use App\Entity\Clan\ClanProposition;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClanProposition>
 */
class ClanPropositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClanProposition::class);
    }

    public function getPropositionByUsers(?UserInterface $recruteur = null, ?User $postulant = null): ?ClanProposition
    {
        $query = $this->createQueryBuilder('cp');

        if (isset($recruteur)) {
            $query->where('cp.recruteur = :recruteur')
                ->setParameter('recruteur', $recruteur);
        }
        if (isset($postulant)) {
            $query->andWhere('cp.postulant = :postulant')
                ->setParameter('postulant', $postulant);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getWaitingPropositionByUsers(?UserInterface $recruteur = null, ?User $postulant = null): ?ClanProposition
    {
        $query = $this->createQueryBuilder('cp')
            ->where('cp.etat = 0');

        if (isset($recruteur)) {
            $query->andWhere('cp.recruteur = :recruteur')
                ->setParameter('recruteur', $recruteur);
        }
        if (isset($postulant)) {
            $query->andWhere('cp.postulant = :postulant')
                ->setParameter('postulant', $postulant);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @return ?array<int, ClanProposition>
     */
    public function getPropositionByRecruteur(?UserInterface $recruteur = null): ?array
    {
        if (isset($recruteur)) {
            $query = $this->createQueryBuilder('cp');
            $query->where('cp.recruteur = :recruteur')
                ->setParameter('recruteur', $recruteur)
                ->orderBy('cp.dateAjout', 'DESC');

            return $query->getQuery()->getResult();
        }

        return null;
    }

    public function getPropositionByPostulant(?UserInterface $postulant = null): ?ClanProposition
    {
        if (isset($postulant)) {
            $query = $this->createQueryBuilder('cp');
            $query->where('cp.postulant = :postulant')
                ->setParameter('postulant', $postulant)
                ->orderBy('cp.dateAjout', 'DESC');

            return $query->getQuery()->getResult();
        }

        return null;
    }

    public function getNumPropositionsByPostulant(?UserInterface $postulant = null): int
    {
        if (isset($postulant)) {
            $query = $this->createQueryBuilder('cp')
                ->select('COUNT(cp)')
                ->where('cp.postulant = :postulant')
                ->andWhere('cp.etat = 0')
                ->setParameter('postulant', $postulant)
                ->getQuery();

            return (int) $query->getSingleScalarResult();
        }

        return 0;
    }
}
