<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Type\ThreadType;
use App\Form\Type\EventType;
use App\Form\Type\CommentType;
use App\Entity\User\User;
use App\Entity\Forum\Forum;
use App\Entity\Forum\Thread;
use App\Entity\Forum\Comment;

class ForumController extends Controller
{
    public function oldMessage(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $thread = $em->getRepository('App\Entity\Forum\Thread')->findOneBy(array('old_id' => (int)$request->get('ID')));
        if(!$thread){
            $comment = $em->getRepository('App\Entity\Forum\Comment')->findOneBy(array('old_id' => (int)$request->get('ID')));
            if($comment)
                $thread = $comment->getThread();
        }

        if(!$thread){
            throw new NotFoundHttpException($this->get('translator')->trans('description.error404.message'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
            'forum_nom' => $thread->getForum()->getSlug(),
            'thread_nom' => $thread->getSlug(),
            'page' => 1
        )));
    }

    public function oldForum(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $forum = $em->getRepository('App\Entity\Forum\Forum')->findOneBy(array('old_id' => (int)$request->get('ID')));

        if(!$forum){
            throw new NotFoundHttpException($this->get('translator')->trans('description.error404.forum'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_topic', array(
            'forum_nom' => $forum->getSlug(),
            'page' => 1
        )));
    }

    public function event($page)
    {
        $em = $this->getDoctrine()->getManager();

        $num = $this->container->getParameter('numReponse');
        $page = max(1, $page);

        $threads = $em->getRepository(Thread::class)->getEvents($num, $page);
        $forum = null;
        if(!empty($threads)){
            $firstT = current($threads->getIterator());
            $forum = $firstT->getForum();
        }

        return $this->render('forum/event.html.twig', array(
            'forum' => $forum,
            'threads' => $threads,
            'page' => $page,
            'nombrePage' => ceil(count($threads)/$num)
        ));
    }

    public function eventAjouter(Request $request)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($authorizationChecker->isGranted('ROLE_ADMIN') !== false || $authorizationChecker->isGranted('ROLE_MODERATOR') !== false){
                $thread = new Thread();
                $thread->setAuthor($user);
                $forum = $this->getDoctrine()->getManager()->getRepository(Forum::class)->getForum('nouveautes')[0];
                $thread->setForum($forum);
                $thread->setIsEvent(true);
                $form = $this->createForm(EventType::class, $thread);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('event', array_merge(
                        $request->request->get('event'),
                        array('body' => $request->get('event_body'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($thread);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.topic.ajoutOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_event'));
                    }
                }
                return $this->render('forum/event.form.html.twig', array(
                    'form' => $form->createView()
                ));
            }
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function eventModifier(Request $request, Thread $thread)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($thread->getAuthor() == $user || $authorizationChecker->isGranted('ROLE_ADMIN') !== false || $authorizationChecker->isGranted('ROLE_MODERATOR') !== false){
                $form = $this->createForm(EventType::class, $thread);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('event', array_merge(
                        $request->request->get('event'),
                        array('body' => $request->get('event_body'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($thread);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.topic.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
                            'forum_nom' => $thread->getForum()->getSlug(),
                            'thread_nom' => $thread->getSlug()
                        )));
                    }
                }
                return $this->render('forum/event.form.html.twig', array(
                    'thread' => $thread,
                    'form' => $form->createView()
                ));
            }
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    public function forum()
    {
        $em = $this->getDoctrine()->getManager();

        $allForums = $em->getRepository(Forum::class)->getForum('');
        $forums = array();
        foreach($allForums as $forum){
            $threads = $em->getRepository(Thread::class)->getThreads($forum, 5, 1);
            if(count($threads)>0){
                $forum->threads = $threads;
                $forums[] = $forum;
            }
        }

        return $this->render('forum/forum.html.twig', array('forums' => $forums));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     */
    public function topic(Forum $forum, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $num = $this->container->getParameter('numReponse');
        $page = max(1, $page);

        $threads = $em->getRepository(Thread::class)->getThreads($forum, $num, $page);

        return $this->render('forum/topic.html.twig', array(
            'forum' => $forum,
            'threads' => $threads,
            'page' => $page,
            'nombrePage' => ceil(count($threads)/$num)
        ));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function thread(Forum $forum, Thread $thread, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $num = $this->container->getParameter('numReponse');
        $page = max(1, $page);

        $comments = $em->getRepository(Comment::class)->getCommentsByThread($thread, $num, $page);

        $form = $this->createForm(CommentType::class, new Comment());

        return $this->render('forum/thread.html.twig', array(
            'forum' => $forum,
            'thread' => $thread,
            'comments' => $comments,
            'page' => $page,
            'nombrePage' => ceil($thread->getNumComments()/$num),
            'form_comment' => $form->createView()
        ));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     */
    public function threadAjouter(Request $request, Forum $forum)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum) || $forum->getCanUserCreateThread()){
                $thread = new Thread();
                $thread->setAuthor($user);
                $thread->setForum($forum);
                $form = $this->createForm(ThreadType::class, $thread);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('thread', array_merge(
                        $request->request->get('thread'),
                        array('body' => $request->get('thread_body'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($thread);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.topic.ajoutOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
                            'forum_nom' => $forum->getSlug(),
                            'thread_nom' => $thread->getSlug()
                        )));
                    }
                }
                return $this->render('forum/thread.form.html.twig', array(
                    'forum' => $forum,
                    'form' => $form->createView()
                ));
            }
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadModifier(Request $request, Forum $forum, Thread $thread)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum) || $thread->getAuthor() == $user){
                $form = $this->createForm(ThreadType::class, $thread);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('thread', array_merge(
                        $request->request->get('thread'),
                        array('body' => $request->get('thread_body'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($thread);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.topic.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
                            'forum_nom' => $forum->getSlug(),
                            'thread_nom' => $thread->getSlug()
                        )));
                    }
                }
                return $this->render('forum/thread.form.html.twig', array(
                    'forum' => $forum,
                    'thread' => $thread,
                    'form' => $form->createView()
                ));
            }
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadVerrouiller(Forum $forum, Thread $thread)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum)){
                $em = $this->getDoctrine()->getManager();
                $thread->setIsCommentable(
                    !$thread->getIsCommentable()
                );
                $em->persist($thread);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $thread->getIsCommentable()?$this->get('translator')->trans('notice.topic.deverrouilleOk'):$this->get('translator')->trans('notice.topic.verrouilleOk')
                );
            }
        }
        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug()
        )));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadPostit(Forum $forum, Thread $thread)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum)){
                $em = $this->getDoctrine()->getManager();
                $thread->setIsPostit(
                    !$thread->getIsPostit()
                );
                $em->persist($thread);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $thread->getIsPostit()?$this->get('translator')->trans('notice.topic.postitOk'):$this->get('translator')->trans('notice.topic.unpostitOk')
                );
            }
        }
        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug()
        )));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function threadSupprimer(Forum $forum, Thread $thread)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum) || $thread->getAuthor() == $user){
                $isEvent = $thread->getIsEvent();

                $em = $this->getDoctrine()->getManager();
                $em->remove($thread);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans('notice.topic.deleteOk')
                );

                if(!$forum->getClan()){
                    if($isEvent)
                        return $this->redirect($this->generateUrl('ninja_tooken_event'));
                    else
                        return $this->redirect($this->generateUrl('ninja_tooken_topic', array(
                            'forum_nom' => $forum->getSlug()
                        )));
                }else
                    return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                        'clan_nom' => $forum->getClan()->getSlug()
                    )));
            }
        }
        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug()
        )));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     */
    public function commentAjouter(Request $request, Forum $forum, Thread $thread, $page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        $page = max(1, $page);
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum) || $thread->getIsCommentable()){
                $comment = new Comment();
                $comment->setAuthor($user);
                $comment->setThread($thread);

                $form = $this->createForm(CommentType::class, $comment);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('comment', array_merge(
                        $request->request->get('comment'),
                        array('body' => $request->get('comment_body'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($comment);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.comment.ajoutOk')
                        );
                    }
                }
            }
        }
        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
            'page' => $page
        )));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     * @ParamConverter("comment", class="App\Entity\Forum\Comment", options={"mapping": {"comment_id":"id"}})
     */
    public function commentModifier(Request $request, Forum $forum, Thread $thread, Comment $comment, $page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        $page = max(1, $page);
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum) || ($thread->getIsCommentable() && $comment->getAuthor() == $user)){
                $form = $this->createForm(CommentType::class, $comment);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('comment', array_merge(
                        $request->request->get('comment'),
                        array('body' => $request->get('comment_body'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($comment);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.comment.editOk')
                        );
                        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
                            'forum_nom' => $forum->getSlug(),
                            'thread_nom' => $thread->getSlug(),
                            'page' => $page
                        )));
                    }
                }
                return $this->render('forum/comment.form.html.twig', array(
                    'forum' => $forum,
                    'thread' => $thread,
                    'comment' => $comment,
                    'page' => $page,
                    'form' => $form->createView()
                ));
            }
            return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
                'forum_nom' => $forum->getSlug(),
                'thread_nom' => $thread->getSlug(),
                'page' => $page
            )));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("forum", class="App\Entity\Forum\Forum", options={"mapping": {"forum_nom":"slug"}})
     * @ParamConverter("thread", class="App\Entity\Forum\Thread", options={"mapping": {"thread_nom":"slug"}})
     * @ParamConverter("comment", class="App\Entity\Forum\Threadt", options={"mapping": {"comment_id":"id"}})
     */
    public function commentSupprimer(Request $request, Forum $forum, Thread $thread, Comment $comment, $page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        $page = max(1, $page);
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if($this->globalRight($authorizationChecker, $user, $forum) || ($thread->getIsCommentable() && $comment->getAuthor() == $user)){
                $em = $this->getDoctrine()->getManager();
                $em->remove($comment);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                     $this->get('translator')->trans('notice.comment.deleteOk')
                );
            }
        }
        return $this->redirect($this->generateUrl('ninja_tooken_thread', array(
            'forum_nom' => $forum->getSlug(),
            'thread_nom' => $thread->getSlug(),
            'page' => $page
        )));
    }

    public function recentComments($max = 10, Forum $forum = null, User $user = null)
    {
        $em = $this->getDoctrine()->getManager();
        $lastComments = $em->getRepository(Comment::class)->getRecentComments($forum, $user, $max);
        return $this->render('forum/comments/recentList.html.twig', array(
            'comments' => $lastComments
        ));
    }

    public function recentThreads($max = 10)
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();
        $threadRepo = $em->getRepository(Thread::class);
        $commentRepo = $em->getRepository(Comment::class);

        $request = "(SELECT nt_thread.id, 'thread' as type, nt_thread.date_ajout FROM nt_thread JOIN nt_forum ON nt_forum.id=nt_thread.forum_id AND nt_forum.clan_id IS NULL ORDER BY nt_thread.date_ajout DESC LIMIT 0,10)".
                   " UNION ".
                   "(SELECT nt_comment.id, 'comment' as type, nt_comment.date_ajout FROM nt_comment JOIN nt_thread ON nt_thread.id=nt_comment.thread_id JOIN nt_forum ON nt_forum.id=nt_thread.forum_id AND nt_forum.clan_id IS NULL ORDER BY nt_comment.date_ajout DESC LIMIT 0,10)".
                   "ORDER BY date_ajout DESC LIMIT 0,10";
        $stmt = $conn->prepare($request);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $data = array();
        foreach($results as $result){
            if($result['type']=='thread')
                $data[] = $threadRepo->findOneById($result['id']);
            else
                $data[] = $commentRepo->findOneById($result['id']);
        }

        return $this->render('forum/lasts/recentList.html.twig', array(
            'lasts' => $data
        ));
    }

    public function globalRight($authorizationChecker=null, User $user=null, Forum $forum=null){
        if($user && $authorizationChecker){
            if($authorizationChecker->isGranted('ROLE_ADMIN') !== false || $authorizationChecker->isGranted('ROLE_MODERATOR') !== false)
                return true;
            if($forum){
                $clan = $forum->getClan();
                if($clan){
                    $clanutilisateur = $user->getClan();
                    if($clanutilisateur)
                        return $clanutilisateur->getClan()==$clan && ($clanutilisateur->getCanEditClan() || $clanutilisateur->getDroit()==0);
                }
            }
        }
        return false;
    }
}
