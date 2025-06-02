<?php

namespace App\Listener;

use App\Entity\Clan\ClanPostulation;
use App\Entity\User\Message;
use App\Entity\User\MessageUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClanPostulationListener
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    // envoie un message pour prévenir le clan
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof ClanPostulation) {
            $this->sendMessage($entity, $em);
        }
    }

    // met à jour la date de changement de l'état
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof ClanPostulation) {
            if ($args->hasChangedField('etat')) {
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();

                $this->sendMessage($entity, $em);

                $entity->setDateChangementEtat(new \DateTime());
                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata("App\Entity\Clan\ClanPostulation"),
                    $entity
                );
            }
        }
    }

    // envoi un message à tous les recruteurs potentiels
    public function sendMessage(ClanPostulation $clanProposition, EntityManagerInterface $em): void
    {
        $message = new Message();

        $message->setAuthor($clanProposition->getPostulant());
        $message->setNom($this->translator->trans('mail.recrutement.nouveau.sujet'));

        $content = $this->translator->trans('description.recrutement.postulation'.(0 === $clanProposition->getEtat() ? 'Add' : 'Remove'));
        $message->setContent($content);

        // envoi aux membres du clan pouvant recruter
        $membres = $clanProposition->getClan()->getMembres();
        foreach ($membres as $membre) {
            if ($membre->getDroit() < 3) {
                $messageuser = new MessageUser();
                $messageuser->setDestinataire($membre->getMembre());
                $message->addReceiver($messageuser);
                $em->persist($messageuser);
            }
        }

        $em->persist($message);
        $em->flush();
    }
}
