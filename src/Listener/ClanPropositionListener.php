<?php

namespace App\Listener;

use App\Entity\Clan\ClanProposition;
use App\Entity\User\Message;
use App\Entity\User\MessageUser;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClanPropositionListener
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    // met à jour la date de changement de l'état
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ClanProposition) {
            if ($args->hasChangedField('etat')) {
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();

                $entity->setDateChangementEtat(new \DateTime());
                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata("App\Entity\Clan\ClanProposition"),
                    $entity
                );
            }
        }
    }

    // averti de l'annulation d'une proposition
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof ClanProposition) {
            if (0 == $entity->getEtat()) {
                $em = $args->getEntityManager();

                $message = new Message();
                $message->setAuthor($entity->getRecruteur());
                $message->setNom($this->translator->trans('mail.recrutement.nouveau.sujet'));
                $message->setContent($this->translator->trans('description.recrutement.propositionRemove'));

                $messageuser = new MessageUser();
                $messageuser->setDestinataire($entity->getPostulant());
                $message->addReceiver($messageuser);

                $em->persist($messageuser);
                $em->persist($message);
                $em->flush();
            }
        }
    }
}
