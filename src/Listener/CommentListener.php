<?php

namespace App\Listener;

use App\Entity\Forum\Comment;
use App\Repository\CommentRepository;
use App\Utils\Akismet;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;

class CommentListener
{
    protected bool $akismetActive;
    protected string $akismetKey;
    protected string $akismetUrl;
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, bool $akismetActive = false, string $akismetKey = '', string $akismetUrl = '')
    {
        $this->akismetActive = $akismetActive;
        $this->akismetKey = $akismetKey;
        $this->akismetUrl = $akismetUrl;
        $this->requestStack = $requestStack;
    }

    // vérification akismet du commentaire
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Comment) {
            // si la vérification akismet est activée
            if ($this->akismetActive) {
                if (null !== $entity->getThread()) {
                    $request = $this->requestStack->getCurrentRequest();
                    $akismet = new Akismet();
                    $akismet->setUserAgent('rzeka.net/1.0.0 | Akismet/1.0.0');
                    $akismet->keyCheck(
                        $this->akismetKey,
                        $this->akismetUrl
                    );
                    // on check qu'il ne s'agit pas d'un spam
                    if (!$akismet->check([
                        'permalink' => $request->getUri(),
                        'user_ip' => $request->getClientIp(),
                        'user_agent' => $request->server->get('HTTP_USER_AGENT'),
                        'referrer' => $request->server->get('HTTP_REFERER'),
                        'comment_type' => 'comment',
                        'comment_author' => $entity->getAuthor()->getUsername(),
                        'comment_author_email' => $entity->getAuthor()->getEmail(),
                        'comment_author_url' => '',
                        'comment_content' => $entity->getBody(),
                    ])) {
                        // annule l'ajout
                        $em->detach($entity);
                    }
                }
            }
        }
    }

    // met à jour le thread
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Comment) {
            if (null !== $entity->getThread()) {
                $thread = $entity->getThread();
                $thread->setLastCommentBy($entity->getAuthor());
                $thread->incrementNumComments();
                $thread->setLastCommentAt($entity->getDateAjout());
                $em->persist($thread);
                $em->flush();
            }
        }
    }

    // met à jour le thread
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Comment) {
            // retire du thread
            if (null !== $entity->getThread()) {
                $thread = $entity->getThread();
                $thread->incrementNumComments(-1);
                // met à jour les références vers le message précédent
                if (0 == $thread->getNumComments()) {
                    $thread->setLastCommentBy(null);
                    $thread->setLastCommentAt($thread->getDateAjout());
                } else {
                    if ($thread->getLastCommentBy() === $entity->getAuthor() && $thread->getLastCommentAt() == $entity->getDateAjout()) {
                        /** @var CommentRepository $commentRepository */
                        $commentRepository = $em->getRepository(Comment::class);
                        $lastComment = $commentRepository->getCommentsByThread($thread, 1, 1);
                        if ($lastComment) {
                            $lastComment = current($lastComment);
                            $thread->setLastCommentBy($lastComment->getAuthor());
                            $thread->setLastCommentAt($lastComment->getDateAjout());
                        } else {
                            $thread->setLastCommentBy(null);
                            $thread->setLastCommentAt($thread->getDateAjout());
                        }
                    }
                }
                $em->persist($thread);
                $em->flush();
            }
        }
    }
}
