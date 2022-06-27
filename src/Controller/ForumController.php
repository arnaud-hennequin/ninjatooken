<?php

namespace App\Controller;

use App\Entity\Forum\Comment;
use App\Entity\Forum\Forum;
use App\Entity\Forum\Thread;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Form\Type\CommentType;
use App\Form\Type\EventType;
use App\Form\Type\ThreadType;
use App\Repository\CommentRepository;
use App\Repository\ForumRepository;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForumController extends AbstractController
{
    protected ?FlashBagInterface $flashBag;
    protected ?UserInterface $user;
    protected ?AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker)
    {
        if ($tokenStorage->getToken()?->getUser() instanceof User) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
        if ($requestStack->getSession() instanceof Session) {
            $this->flashBag = $requestStack->getSession()->getFlashBag();
        }
        $this->authorizationChecker = $authorizationChecker;
    }

    public function oldMessage(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): RedirectResponse
    {
        $thread = $em->getRepository('App\Entity\Forum\Thread')->findOneBy(['old_id' => (int) $request->get('ID')]);
        if (!$thread) {
            $comment = $em->getRepository('App\Entity\Forum\Comment')->findOneBy(['old_id' => (int) $request->get('ID')]);
            if ($comment) {
                $thread = $comment->getThread();
            }
        }

        if (!$thread) {
            throw new NotFoundHttpException($translator->trans('description.error404.message'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $thread->getForum()->getSlug(),
            'thread_nom' => $thread->getSlug(),
            'page' => 1,
        ]));
    }

    public function oldForum(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): RedirectResponse
    {
        $forum = $em->getRepository('App\Entity\Forum\Forum')->findOneBy(['old_id' => (int) $request->get('ID')]);

        if (!$forum) {
            throw new NotFoundHttpException($translator->trans('description.error404.forum'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_topic', [
            'forum_nom' => $forum->getSlug(),
            'page' => 1,
        ]));
    }

    public function event(EntityManagerInterface $em, $page): Response
    {
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        /** @var ThreadRepository $threadRepository */
        $threadRepository = $em->getRepository(Thread::class);
        $threads = $threadRepository->getEvents($num, $page);
        $forum = $threads->getIterator()->current()->getForum();

        return $this->render('forum/event.html.twig', [
            'forum' => $forum,
            'threads' => $threads,
            'page' => $page,
            'nombrePage' => ceil(count($threads) / $num),
        ]);
    }

    public function eventAjouter(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if (false !== $this->authorizationChecker->isGranted('ROLE_ADMIN') || false !== $this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
                $thread = new Thread();
                $thread->setAuthor($this->user);
                /** @var ForumRepository $forumRepository */
                $forumRepository = $em->getRepository(Forum::class);
                $forum = $forumRepository->getForum('nouveautes')[0];
                $thread->setForum($forum);
                $thread->setIsEvent(true);
                $form = $this->createForm(EventType::class, $thread);
                if (Request::METHOD_POST === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('event', array_merge(
                        (array) $request->request->get('event'),
                        ['body' => $request->get('event_body')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em->persist($thread);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.topic.ajoutOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_event'));
                    }
                }

                return $this->render('forum/event.form.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function eventModifier(Request $request, TranslatorInterface $translator, Thread $thread, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($thread->getAuthor() === $this->user || false !== $this->authorizationChecker->isGranted('ROLE_ADMIN') || false !== $this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
                $form = $this->createForm(EventType::class, $thread);
                if (Request::METHOD_POST === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('event', array_merge(
                        (array) $request->request->get('event'),
                        ['body' => $request->get('event_body')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em->persist($thread);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.topic.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
                            'forum_nom' => $thread->getForum()->getSlug(),
                            'thread_nom' => $thread->getSlug(),
                        ]));
                    }
                }

                return $this->render('forum/event.form.html.twig', [
                    'thread' => $thread,
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function forum(EntityManagerInterface $em): Response
    {
        /** @var ThreadRepository $threadRepository */
        $threadRepository = $em->getRepository(Thread::class);
        /** @var ForumRepository $forumRepository */
        $forumRepository = $em->getRepository(Forum::class);
        $allForums = $forumRepository->getForum('');
        $forums = [];
        foreach ($allForums as $forum) {
            $threads = $threadRepository->getThreads($forum, 5, 1);
            if (count($threads) > 0) {
                $forum->threads = $threads;
                $forums[] = $forum;
            }
        }

        return $this->render('forum/forum.html.twig', ['forums' => $forums]);
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     */
    public function topic(Forum $forum, EntityManagerInterface $em, $page): Response
    {
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        /** @var ThreadRepository $threadRepository */
        $threadRepository = $em->getRepository(Thread::class);
        $threads = $threadRepository->getThreads($forum, $num, $page);

        return $this->render('forum/topic.html.twig', [
            'forum' => $forum,
            'threads' => $threads,
            'page' => $page,
            'nombrePage' => ceil(count($threads) / $num),
        ]);
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function thread(Forum $forum, Thread $thread, EntityManagerInterface $em, $page): Response
    {
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        /** @var CommentRepository $commentRepository */
        $commentRepository = $em->getRepository(Comment::class);
        $comments = $commentRepository->getCommentsByThread($thread, $num, $page);

        $form = $this->createForm(CommentType::class, new Comment());

        return $this->render('forum/thread.html.twig', [
            'forum' => $forum,
            'thread' => $thread,
            'comments' => $comments,
            'page' => $page,
            'nombrePage' => ceil($thread->getNumComments() / $num),
            'form_comment' => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     */
    public function threadAjouter(Request $request, TranslatorInterface $translator, Forum $forum, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum) || $forum->getCanUserCreateThread()) {
                $thread = new Thread();
                $thread->setAuthor($this->user);
                $thread->setForum($forum);
                $form = $this->createForm(ThreadType::class, $thread);
                if ('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('thread', array_merge(
                        (array) $request->request->get('thread'),
                        ['body' => $request->get('thread_body')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em->persist($thread);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.topic.ajoutOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
                            'forum_nom' => $forum->getSlug(),
                            'thread_nom' => $thread->getSlug(),
                        ]));
                    }
                }

                return $this->render('forum/thread.form.html.twig', [
                    'forum' => $forum,
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadModifier(Request $request, TranslatorInterface $translator, Forum $forum, Thread $thread, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum) || $thread->getAuthor() === $this->user) {
                $form = $this->createForm(ThreadType::class, $thread);
                if (Request::METHOD_POST === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('thread', array_merge(
                        (array) $request->request->get('thread'),
                        ['body' => $request->get('thread_body')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em->persist($thread);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.topic.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
                            'forum_nom' => $forum->getSlug(),
                            'thread_nom' => $thread->getSlug(),
                        ]));
                    }
                }

                return $this->render('forum/thread.form.html.twig', [
                    'forum' => $forum,
                    'thread' => $thread,
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadVerrouiller(TranslatorInterface $translator, Forum $forum, Thread $thread, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum)) {
                $thread->setIsCommentable(
                    !$thread->getIsCommentable()
                );
                $em->persist($thread);
                $em->flush();

                $this->flashBag?->add(
                    'notice',
                    $thread->getIsCommentable() ? $translator->trans('notice.topic.deverrouilleOk') : $translator->trans('notice.topic.verrouilleOk')
                );
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
        ]));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadPostit(TranslatorInterface $translator, Forum $forum, Thread $thread, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum)) {
                $thread->setIsPostit(
                    !$thread->getIsPostit()
                );
                $em->persist($thread);
                $em->flush();

                $this->flashBag?->add(
                    'notice',
                    $thread->getIsPostit() ? $translator->trans('notice.topic.postitOk') : $translator->trans('notice.topic.unpostitOk')
                );
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
        ]));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadSupprimer(TranslatorInterface $translator, Forum $forum, Thread $thread, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum) || $thread->getAuthor() === $this->user) {
                $isEvent = $thread->getIsEvent();

                $em->remove($thread);
                $em->flush();

                $this->flashBag?->add(
                    'notice',
                    $translator->trans('notice.topic.deleteOk')
                );

                if (!$forum->getClan()) {
                    if ($isEvent) {
                        return $this->redirect($this->generateUrl('ninja_tooken_event'));
                    } else {
                        return $this->redirect($this->generateUrl('ninja_tooken_topic', [
                            'forum_nom' => $forum->getSlug(),
                        ]));
                    }
                } else {
                    return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                        'clan_nom' => $forum->getClan()->getSlug(),
                    ]));
                }
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
        ]));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function commentAjouter(Request $request, TranslatorInterface $translator, Forum $forum, Thread $thread, EntityManagerInterface $em, $page): RedirectResponse
    {
        $page = max(1, $page);
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum) || $thread->getIsCommentable()) {
                $comment = new Comment();
                $comment->setAuthor($this->user);
                $comment->setThread($thread);

                $form = $this->createForm(CommentType::class, $comment);
                if ('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('comment', array_merge(
                        (array) $request->request->get('comment'),
                        ['body' => $request->get('comment_body')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em->persist($comment);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.comment.ajoutOk')
                        );
                    }
                }
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
            'page' => $page,
        ]));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     * @ParamConverter("comment", class="App\Entity\Forum\Comment", options={"mapping": {"comment_id":"id"}})
     */
    public function commentModifier(Request $request, TranslatorInterface $translator, Forum $forum, Thread $thread, Comment $comment, EntityManagerInterface $em, $page): Response
    {
        $page = max(1, $page);
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum) || ($thread->getIsCommentable() && $comment->getAuthor() === $this->user)) {
                $form = $this->createForm(CommentType::class, $comment);
                if (Request::METHOD_POST === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('comment', array_merge(
                        (array) $request->request->get('comment'),
                        ['body' => $request->get('comment_body')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em->persist($comment);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.comment.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
                            'forum_nom' => $forum->getSlug(),
                            'thread_nom' => $thread->getSlug(),
                            'page' => $page,
                        ]));
                    }
                }

                return $this->render('forum/comment.form.html.twig', [
                    'forum' => $forum,
                    'thread' => $thread,
                    'comment' => $comment,
                    'page' => $page,
                    'form' => $form->createView(),
                ]);
            }

            return $this->redirect($this->generateUrl('ninja_tooken_thread', [
                'forum_nom' => $forum->getSlug(),
                'thread_nom' => $thread->getSlug(),
                'page' => $page,
            ]));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     * @ParamConverter("comment", class="App\Entity\Forum\Comment", options={"mapping": {"comment_id":"id"}})
     */
    public function commentSupprimer(TranslatorInterface $translator, Forum $forum, Thread $thread, Comment $comment, EntityManagerInterface $em, $page): RedirectResponse
    {
        $page = max(1, $page);
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->globalRight($forum) || ($thread->getIsCommentable() && $comment->getAuthor() === $this->user)) {
                $em->remove($comment);
                $em->flush();

                $this->flashBag?->add(
                    'notice',
                     $translator->trans('notice.comment.deleteOk')
                );
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
            'page' => $page,
        ]));
    }

    public function recentComments(CommentRepository $commentRepository, $max = 10, Forum $forum = null): Response
    {
        return $this->render('forum/comments/recentList.html.twig', [
            'comments' => $commentRepository->getRecentComments($forum, null, $max),
        ]);
    }

    public function recentThreads(EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $threadRepo = $em->getRepository(Thread::class);
        $commentRepo = $em->getRepository(Comment::class);

        $request = "(SELECT nt_thread.id, 'thread' as type, nt_thread.date_ajout FROM nt_thread JOIN nt_forum ON nt_forum.id=nt_thread.forum_id AND nt_forum.clan_id IS NULL ORDER BY nt_thread.date_ajout DESC LIMIT 0,10)".
                   ' UNION '.
                   "(SELECT nt_comment.id, 'comment' as type, nt_comment.date_ajout FROM nt_comment JOIN nt_thread ON nt_thread.id=nt_comment.thread_id JOIN nt_forum ON nt_forum.id=nt_thread.forum_id AND nt_forum.clan_id IS NULL ORDER BY nt_comment.date_ajout DESC LIMIT 0,10)".
                   'ORDER BY date_ajout DESC LIMIT 0,10';
        $stmt = $conn->prepare($request);
        $result = $stmt->executeQuery();
        $results = $result->fetchAllAssociative();
        $data = [];
        foreach ($results as $result) {
            if ('thread' == $result['type']) {
                $data[] = $threadRepo->findOneBy(['id' => $result['id']]);
            } else {
                $data[] = $commentRepo->findOneBy(['id' => $result['id']]);
            }
        }

        return $this->render('forum/lasts/recentList.html.twig', [
            'lasts' => $data,
        ]);
    }

    public function globalRight(Forum $forum = null): bool
    {
        if ($this->user && $this->authorizationChecker) {
            if (false !== $this->authorizationChecker->isGranted('ROLE_ADMIN') || false !== $this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
                return true;
            }
            if ($forum && ($clan = $forum->getClan()) !== null && ($clanutilisateur = $this->user->getClan()) !== null) {
                return ($clanutilisateur->getClan() === $clan) && ($clanutilisateur->getCanEditClan() || 0 == $clanutilisateur->getDroit());
            }
        }

        return false;
    }
}
