<?php
namespace App\Entity\Clan;
 
use Doctrine\ORM\EntityRepository;
use App\Entity\Clan\Clan;
use App\Entity\User\User;
 
class ClanPropositionRepository extends EntityRepository
{

    public function getPropositionByUsers(User $recruteur=null, User $postulant=null)
    {
        $query = $this->createQueryBuilder('cp');

        if(isset($recruteur)){
            $query->where('cp.recruteur = :recruteur')
                ->setParameter('recruteur', $recruteur);
        }
        if(isset($postulant)){
            $query->andWhere('cp.postulant = :postulant')
                ->setParameter('postulant', $postulant);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getWaitingPropositionByUsers(User $recruteur=null, User $postulant=null)
    {
        $query = $this->createQueryBuilder('cp')
            ->where('cp.etat = 0');

        if(isset($recruteur)){
            $query->andWhere('cp.recruteur = :recruteur')
                ->setParameter('recruteur', $recruteur);
        }
        if(isset($postulant)){
            $query->andWhere('cp.postulant = :postulant')
                ->setParameter('postulant', $postulant);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getPropositionByRecruteur(User $recruteur=null)
    {
        if(isset($recruteur)){
            $query = $this->createQueryBuilder('cp');
            $query->where('cp.recruteur = :recruteur')
                ->setParameter('recruteur', $recruteur)
                ->orderBy('cp.dateAjout', 'DESC');
            return $query->getQuery()->getResult();
        }
        return null;
    }

    public function getPropositionByPostulant(User $postulant=null)
    {
        if(isset($postulant)){
            $query = $this->createQueryBuilder('cp');
            $query->where('cp.postulant = :postulant')
                ->setParameter('postulant', $postulant)
                ->orderBy('cp.dateAjout', 'DESC');
            return $query->getQuery()->getResult();
        }
        return null;
    }

    public function getNumPropositionsByPostulant(User $postulant=null)
    {
        if(isset($postulant)){
            $query = $this->createQueryBuilder('cp')
                ->select('COUNT(cp)')
                ->where('cp.postulant = :postulant')
                ->andWhere('cp.etat = 0')
                ->setParameter('postulant', $postulant)
                ->getQuery();
            return $query->getSingleScalarResult();
        }
        return 0;
    }
}