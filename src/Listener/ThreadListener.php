<?php

namespace App\Listener;

use App\Entity\Forum\Thread;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ThreadListener
{
    // supprime les commentaires associés
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Thread) {
            // retire du thread
            if (null !== $entity->getForum()) {
                $forum = $entity->getForum();
                $forum->incrementNumThreads(-1);
                $em->persist($forum);
                $em->flush();
            }
        }
    }

    // met à jour le forum
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Thread) {
            if (null !== $entity->getForum()) {
                $forum = $entity->getForum();
                $forum->incrementNumThreads();
                $em->persist($forum);
                $em->flush();
            }
        }
    }
}
