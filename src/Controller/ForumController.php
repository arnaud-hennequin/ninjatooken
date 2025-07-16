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
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
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
        if ($requestStack->getSession() instanceof FlashBagAwareSessionInterface) {
            $this->flashBag = $requestStack->getSession()->getFlashBag();
        }
        $this->authorizationChecker = $authorizationChecker;
    }

    #[Route('/{_locale}/message.php', name: 'ninja_tooken_message_old')]
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

    #[Route('/{_locale}/forum.php', name: 'ninja_tooken_forum_old')]
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

    /**
     * @throws \Exception
     */
    #[Route('/{_locale}/event/{page}', name: 'ninja_tooken_event', methods: ['GET'], defaults: ['page' => 1], requirements: ['page' => '\d*'])]
    public function event(EntityManagerInterface $em, int $page = 1): Response
    {
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        /** @var ThreadRepository $threadRepository */
        $threadRepository = $em->getRepository(Thread::class);
        $threads = $threadRepository->getEvents($num, $page);
        /** @var Thread $thread */
        $thread = $threads->getIterator()->current();
        $forum = $thread?->getForum();

        return $this->render('forum/event.html.twig', [
            'forum' => $forum,
            'threads' => $threads,
            'page' => $page,
            'nombrePage' => ceil(count($threads) / $num),
        ]);
    }

    #[Route('/{_locale}/event/ajouter/', name: 'ninja_tooken_event_ajouter')]
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

    #[Route('/{_locale}/event/{thread_nom}/modifier/', name: 'ninja_tooken_event_modifier')]
    public function eventModifier(
        Request $request,
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em
    ): Response {
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

    #[Route('/{_locale}/forum', name: 'ninja_tooken_forum')]
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
            $forum->setNumThreads(count($threads));
            $forums[] = $forum;
        }

        return $this->render('forum/forum.html.twig', ['forums' => $forums]);
    }

    #[Route('/{_locale}/forum/{forum_nom}/{page}', name: 'ninja_tooken_topic', methods: ['GET'], defaults: ['page' => 1], requirements: ['page' => '\d*'])]
    public function topic(
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        EntityManagerInterface $em,
        int $page = 1
    ): Response {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/{page}', name: 'ninja_tooken_thread', methods: ['GET'], defaults: ['page' => 1], requirements: ['page' => '\d*'])]
    public function thread(
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em,
        int $page = 1
    ): Response {
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

    #[Route('/{_locale}/forum/{forum_nom}/ajouter/', name: 'ninja_tooken_thread_ajouter')]
    public function threadAjouter(
        Request $request,
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        EntityManagerInterface $em
    ): Response {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/modifier/', name: 'ninja_tooken_thread_modifier')]
    public function threadModifier(
        Request $request,
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em
    ): Response {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/lock/', name: 'ninja_tooken_thread_verrouiller')]
    public function threadVerrouiller(
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em
    ): RedirectResponse {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/postit/', name: 'ninja_tooken_thread_postit')]
    public function threadPostit(
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em
    ): RedirectResponse {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/supprimer/', name: 'ninja_tooken_thread_supprimer')]
    public function threadSupprimer(
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em
    ): Response {
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
                    }

                    return $this->redirect($this->generateUrl('ninja_tooken_topic', [
                        'forum_nom' => $forum->getSlug(),
                    ]));
                }

                return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                    'clan_nom' => $forum->getClan()->getSlug(),
                ]));
            }
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', [
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
        ]));
    }

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/ajouter/{page}', name: 'ninja_tooken_comment_ajouter', requirements: ['page' => '\d*'])]
    public function commentAjouter(
        Request $request,
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        EntityManagerInterface $em,
        int $page = 1
    ): RedirectResponse {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/{comment_id}/modifier/{page}', name: 'ninja_tooken_comment_modifier')]
    public function commentModifier(
        Request $request,
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        Comment $comment,
        EntityManagerInterface $em,
        int $page = 1
    ): Response {
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

    #[Route('/{_locale}/forum/{forum_nom}/{thread_nom}/{comment_id}/supprimer/{page}', name: 'ninja_tooken_comment_supprimer')]
    public function commentSupprimer(
        TranslatorInterface $translator,
        #[MapEntity(mapping: ['forum_nom' => 'slug'])]
        Forum $forum,
        #[MapEntity(mapping: ['thread_nom' => 'slug'])]
        Thread $thread,
        Comment $comment,
        EntityManagerInterface $em,
        int $page = 1
    ): RedirectResponse {
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

    public function recentComments(CommentRepository $commentRepository, int $max = 10, #[MapEntity(mapping: ['forum_nom' => 'slug'])] ?Forum $forum = null): Response
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
            if ('thread' === $result['type']) {
                $data[] = $threadRepo->findOneBy(['id' => $result['id']]);
            } else {
                $data[] = $commentRepo->findOneBy(['id' => $result['id']]);
            }
        }

        return $this->render('forum/lasts/recentList.html.twig', [
            'lasts' => $data,
        ]);
    }

    private function globalRight(?Forum $forum = null): bool
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
