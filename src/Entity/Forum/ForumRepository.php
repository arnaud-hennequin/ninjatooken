<?php
namespace App\Entity\Forum;
 
use Doctrine\ORM\EntityRepository;
use App\Entity\Forum\Forum;
use App\Entity\Clan\Clan;
 
class ForumRepository extends EntityRepository
{
    public function getForum($slug="", Clan $clan = null, $nombreParPage=20, $page=1)
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('f');

        if($clan){
            $query->where('f.clan = :clan')
            ->setParameter('clan', $clan);
        }else
            $query->where('f.clan is null');

        if(!empty($slug)){
            $query->andWhere('f.slug = :slug')
            ->setParameter('slug', $slug);
        }

        $query->orderBy('f.ordre', 'DESC')
            ->setFirstResult(($page-1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }
}