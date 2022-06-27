<?php

namespace App\Listener;

use App\Entity\Clan\Clan;
use App\Entity\Clan\ClanProposition;
use App\Entity\Clan\ClanUtilisateur;
use App\Repository\ClanPropositionRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ClanListener
{
    // supprime les propositions
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Clan) {
            $em = $args->getEntityManager();

            $membres = $entity->getMembres();
            if ($membres) {
                $flush = false;
                /** @var ClanPropositionRepository $repo_proposition */
                $repo_proposition = $em->getRepository(ClanProposition::class);
                /** @var ClanUtilisateur $membre */
                foreach ($membres as $membre) {
                    // supprime les propositions de recrutement
                    $propositions = $repo_proposition->getPropositionByRecruteur($membre->getMembre());
                    if ($propositions) {
                        foreach ($propositions as $proposition) {
                            $em->remove($proposition);
                            $flush = true;
                        }
                    }
                }
                if ($flush) {
                    $em->flush();
                }
            }
        }
    }
}
