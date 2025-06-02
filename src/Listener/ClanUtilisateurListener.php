<?php

namespace App\Listener;

use App\Entity\Clan\ClanPostulation;
use App\Entity\Clan\ClanProposition;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Repository\ClanPostulationRepository;
use App\Repository\ClanPropositionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ClanUtilisateurListener
{
    // supprime la liaison vers le clan
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof ClanUtilisateur) {
            if ($entity->getClan()->delete) {
                return;
            }
            $em = $args->getEntityManager();

            // réaffectation des recruts
            $previousUser = $entity->getMembre();
            $previousRecruts = $previousUser->getRecruts();
            if ($previousUser !== null && $previousRecruts !== null && count($previousRecruts) > 0) {
                // le remplaçant (la plus ancienne recrut)
                $substitute = $this->getOldest($previousRecruts, $previousUser); // User

                // ré-affecte les liaisons des recruts
                if ($substitute !== null) {
                    // les droits du membre supprimé
                    $previousDroit = $entity->getDroit();

                    // parcourt les recruts du remplaçant et met à jour les droits
                    $substituteRecruts = $substitute->getRecruts();
                    if ($substituteRecruts !== null && count($substituteRecruts) > 0) {
                        // parcourt les recruts du remplaçant et les ré-assigne
                        foreach ($substituteRecruts as $substituteRecrut) {
                            $substituteRecrutMembre = $substituteRecrut->getMembre();
                            if ($substituteRecrutMembre !== $substitute) {
                                $substituteRecrut->setDroit($previousDroit + 1);

                                // met à jour si avait des recruts
                                $substituteRecrutRecruts = $substituteRecrutMembre->getRecruts();
                                foreach ($substituteRecrutRecruts as $substituteRecrutRecrut) {
                                    $substituteRecrutRecrut->setDroit($previousDroit + 2);
                                    $em->persist($substituteRecrutRecrut);
                                }

                                $em->persist($substituteRecrut);
                            }
                        }
                    }

                    // parcourt les recruts de l'ancien utilisateur et les ré-assigne au remplaçant
                    foreach ($previousRecruts as $previousRecrut) {
                        $previousRecrutMembre = $previousRecrut->getMembre();
                        if ($previousRecrutMembre !== $substitute && $previousRecrutMembre !== $previousUser) {
                            $previousRecrut->setRecruteur($substitute);
                            $previousRecrut->setDroit($previousDroit + 1);

                            // met à jour si avait des recruts
                            $previousRecrutRecruts = $previousRecrutMembre->getRecruts();
                            foreach ($previousRecrutRecruts as $previousRecrutRecrut) {
                                $previousRecrutRecrut->setDroit($previousDroit + 2);
                                $em->persist($previousRecrutRecrut);
                            }

                            $em->persist($previousRecrut);
                        }
                    }

                    // le recruteur du membre supprimé
                    $previousRecruteur = $entity->getRecruteur(); // User
                    if (!$previousRecruteur || $previousRecruteur === $previousUser) {
                        $previousRecruteur = $substitute;
                    }

                    // redéfini le remplaçant
                    $substitute_cu = $substitute->getClan(); // ClanUtilisateur
                    $substitute_cu->setRecruteur($previousRecruteur);
                    $substitute_cu->setDroit($previousDroit);

                    $em->persist($substitute_cu);

                    $em->flush();
                }
            }
        }
    }

    /**
     * @param Collection<int, ClanUtilisateur> $recruts
     */
    public function getOldest(Collection $recruts, UserInterface $recruteur): ?UserInterface
    {
        /** @var ClanUtilisateur $recrut */
        foreach ($recruts as $recrut) {
            $membre = $recrut->getMembre();
            if ($membre !== $recruteur) {
                return $membre;
            }
        }

        return null;
    }

    // met à jour les recruts et supprime les propositions
    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof ClanUtilisateur) {
            if ($entity->getClan()->delete) {
                return;
            }
            $em = $args->getEntityManager();

            // supprime les propositions de recrutement
            /** @var ClanPropositionRepository $clanPropositionRepository */
            $clanPropositionRepository = $em->getRepository(ClanProposition::class);
            $propositions = $clanPropositionRepository->getPropositionByRecruteur($entity->getMembre());
            if ($propositions) {
                foreach ($propositions as $proposition) {
                    $em->remove($proposition);
                }
                $em->flush();
            }
        }
    }

    // supprime la postulation sur le même clan
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof ClanUtilisateur) {
            $clan = $entity->getClan();
            $user = $entity->getMembre();
            if ($clan) {
                /** @var ClanPostulationRepository $clanPostulationRepository */
                $clanPostulationRepository = $em->getRepository(ClanPostulation::class);
                $postulation = $clanPostulationRepository->getByClanUser($clan, $user);
                if ($postulation) {
                    $em->remove($postulation);
                    $em->flush();
                }
            }
        }
    }
}
