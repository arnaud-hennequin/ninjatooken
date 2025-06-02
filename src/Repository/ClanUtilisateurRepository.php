<?php

namespace App\Repository;

use App\Entity\Clan\Clan;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClanUtilisateur>
 */
class ClanUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClanUtilisateur::class);
    }

    /**
     * @return array<int, ClanUtilisateur>|null
     */
    public function getMembres(
        ?Clan $clan = null,
        ?int $droit = null,
        ?UserInterface $recruteur = null,
        int $nombreParPage = 100,
        int $page = 1,
    ): ?array {
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
}
