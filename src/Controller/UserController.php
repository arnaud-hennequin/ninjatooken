<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use App\Form\Type\MessageType;
use App\Form\Type\RegistrationFormType;
use App\Form\ChangePasswordFormType;
use App\Entity\User\User;
use App\Entity\User\Friend;
use App\Entity\User\Capture;
use App\Entity\User\Message;
use App\Entity\User\MessageUser;
use App\Entity\Clan\ClanProposition;
use App\Utils\GameData;

class UserController extends AbstractController
{
    private $tokenManager;

    public function __construct(CsrfTokenManagerInterface $tokenManager = null)
    {
        $this->tokenManager = $tokenManager;
    }

    public function oldUser(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('App\Entity\User\User')->findOneBy(array('old_id' => (int)$request->get('ID')));

        if (!$user) {
            throw new NotFoundHttpException($translator->trans('notice.utilisateur.error404'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_fiche', array(
            'user_nom' => $user->getSlug(),
            'page' => 1
        )));
    }

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('ninja_tooken_homepage');
        }

        return $this->render('user/registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function login(Request $request)
    {
        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $csrfToken = $this->tokenManager
            ? $this->tokenManager->getToken('authenticate')->getValue()
            : null;

        return $this->render('user/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function connected(User $user, GameData $gameData)
    {
        $em = $this->getDoctrine()->getManager();
        $repo_message = $em->getRepository(Message::class);
        $repo_friend = $em->getRepository(Friend::class);
        $repo_propo = $em->getRepository(ClanProposition::class);
        $user->numNewMessage = $repo_message->getNumNewMessages($user);
        $user->numDemandesFriends = $repo_friend->getNumDemandes($user);
        $user->numPropositionsRecrutement = $repo_propo->getNumPropositionsByPostulant($user);

        $ninja = $user->getNinja();
        if ($ninja) {
            // l'expérience (et données associées)
            $gameData->setExperience($ninja->getExperience(), $ninja->getGrade());

            $user->level = $gameData->getLevelActuel();
        }

        return $this->render('user/connected.html.twig', array('user' => $user));
    }

    public function autologin(Request $request, TranslatorInterface $translator, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, $autologin)
    {
        if (!empty($autologin)) {
            $authorizationChecker = $this->get('security.authorization_checker');
            if (!$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->findOneBy(array('autoLogin' => $autologin));
                if (null !== $user && $user->isAccountNonLocked()) {
                    // si l'utilisateur a déjà été connecté avant le dernier garbage collector
                    if ($user->getUpdatedAt()===null || (new \DateTime())->getTimestamp() - $user->getUpdatedAt()->getTimestamp() > ini_get('session.gc_maxlifetime')) {
                        // lance la connexion
                        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
                        $tokenStorage->setToken($token);
                        $event = new InteractiveLoginEvent($request, $token);
                        $eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
                    }
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.autologinKO')
                    );
                }
            }
        }

        // redirige sur l'accueil
        return $this->redirect($this->generateUrl('ninja_tooken_homepage'));
    }

    /**
     * @ParamConverter("user", class="App\Entity\User\User", options={"mapping": {"email":"email"}})
     */
    public function desinscription(TranslatorInterface $translator, User $user)
    {
        if (null !== $user) {
            $em = $this->getDoctrine()->getManager();
            $user->setReceiveNewsletter(false);
            $user->setReceiveAvertissement(false);
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $translator->trans('notice.desinscriptionOK')
            );
        }

        // redirige sur l'accueil
        return $this->redirect($this->generateUrl('ninja_tooken_homepage'));
    }

    /**
     * @ParamConverter("user", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     */
    public function fiche(User $user, $page = 1)
    {
        // amis
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        
        $repo = $this->getDoctrine()->getManager()->getRepository(Friend::class);

        $friends = $repo->getFriends($user, $num, $page);

        return $this->render('user/fiche.html.twig', array(
            'friends' => $friends,
            'page' => $page,
            'nombrePage' => ceil($repo->getNumFriends($user)/$num),
            'user' => $user
        ));
    }

    public function messagerieEnvoi(Request $request, TranslatorInterface $translator, $page=1)
    {
        return $this->getMessagerie($request, $translator, $page, false);
    }

    public function messagerie(Request $request, TranslatorInterface $translator, $page=1)
    {
        return $this->getMessagerie($request, $translator, $page);
    }

    public function getMessagerie(Request $request, TranslatorInterface $translator, $page=1, $reception=true)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('ROLE_USER') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $num = $this->getParameter('numReponse');
            $page = max(1, $page);
            $id = 0;

            $em = $this->getDoctrine()->getManager();
            $repo_message = $em->getRepository(Message::class);

            // est dans l'envoi d'un message ?
            $isNewMessage = (int)$request->get('add')==1;

            // est dans la suppression d'un message ?
            $isDeleteMessage = (int)$request->get('del')==1;

            // l'envoi d'un message
            $form = null;
            if ($isNewMessage) {
                $message = new Message();
                $form = $this->createForm(MessageType::class, $message);
                if ('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('message', array_merge(
                        $request->request->get('message'),
                        array('content' => $request->get('message_content'))
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $destinataires = array();
                        $destis = $request->request->get('destinataires');
                        if ($destis) {
                            $repo_user = $em->getRepository(User::class);
                            foreach($destis as $desti) {
                                $destinataire = $repo_user->findOneById((int)$desti);
                                if ($destinataire)
                                    $destinataires[] = $destinataire;
                            }
                        }

                        if (!empty($destinataires)) {
                            $message->setAuthor($user);
                            foreach($destinataires as $destinataire) {
                                $messageuser = new MessageUser();
                                $messageuser->setDestinataire($destinataire);
                                $message->addReceiver($messageuser);

                                $em->persist($messageuser);
                            }
                            $em->persist($message);

                            $em->flush();
                        }

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $translator->trans('notice.messageEnvoiOk')
                        );
                        return $this->redirect($this->generateUrl($reception?'ninja_tooken_user_messagerie':'ninja_tooken_user_messagerie_envoi', array(
                            'page' => $page
                        )));
                    }
                }
            // lecture - suppression
            } else {

                // cherche un message à afficher
                $id = (int)$request->get('id');
                if (!empty($id))
                    $message = $repo_message->findOneBy(array('id' => $id));
                else{
                    $message = current($reception?$repo_message->getFirstReceiveMessage($user):$repo_message->getFirstSendMessage($user));
                    if ($message)
                        $id = $message->getId();
                }

                if ($message) {
                    // en réception
                    if ($reception) {
                        foreach($message->getReceivers() as $receiver) {
                            if ($receiver->getDestinataire() == $user) {
                                // suppression du message
                                if ($isDeleteMessage) {
                                    $receiver->setHasDeleted(true);
                                    $em->persist($receiver);
                                    $em->flush();

                                    $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        $translator->trans('notice.messageSuppressionOk')
                                    );
                                    return $this->redirect($this->generateUrl('ninja_tooken_user_messagerie', array(
                                        'page' => $page
                                    )));
                                }
                                // date de lecture
                                if ($receiver->getDateRead()===null) {
                                    $receiver->setDateRead(new \DateTime('now'));
                                    $em->persist($receiver);
                                    $em->flush();
                                    break;
                                }
                            }
                        }
                    // en envoi : suppression du message
                    }elseif ($isDeleteMessage) {
                        $message->setHasDeleted(true);
                        $em->persist($message);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $translator->trans('notice.messageSuppressionOk')
                        );
                        return $this->redirect($this->generateUrl('ninja_tooken_user_messagerie_envoi', array(
                            'page' => $page
                        )));
                    }
                }
                // le formulaire de réponse d'un message
                if ($message) {
                    $messageform = new Message();
                    $messageform->setNom('Re : '.str_replace('Re : ', '', $message->getNom()));
                    $messageform->setContent('<fieldset><legend>'.$message->getAuthor()->getUsername().'</legend>'.$message->getContent().'</fieldset><p></p>');

                    $form = $this->createForm(MessageType::class, $messageform);
                }
            }

            if ($reception) {
                $messages = $repo_message->getReceiveMessages($user, $num, $page);
                $total = $repo_message->getNumReceiveMessages($user);
            } else {
                $messages = $repo_message->getSendMessages($user, $num, $page);
                $total = $repo_message->getNumSendMessages($user);
            }

            return $this->render('user/messagerie.html.twig', array(
                    'messages' => $messages,
                    'page' => $page,
                    'nombrePage' => ceil($total/$num),
                    'currentmessage' => $message,
                    'id' => $id,
                    'form' => $form?$form->createView():null
                )
            );
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function userFind(Request $request)
    {
        $response = new JsonResponse();
        $users = array();

        if ($request->isXmlHttpRequest()) {
            $user = (string)$request->query->get('q');

            if (!empty($user)) {
                $users = $this->getDoctrine()
                    ->getManager()
                    ->getRepository(User::class)
                    ->searchUser($user, 10, false);
            }
        }

        $response->setData($users);
        return $response;
    }

    public function parametres()
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if ($user->getDateOfBirth()==new \DateTime('0000-00-00 00:00:00'))
                $user->setDateOfBirth(null);

            $form = $this->createForm(ChangePasswordFormType::class);

            return $this->render('user/parametres.html.twig', array(
                'form' => $form->createView()
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresUpdate(Request $request, TranslatorInterface $translator)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $translator = $translator;
            // post request
            if ($request->getMethod() === 'POST') {
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $em = $this->getDoctrine()->getManager();

                $update = false;
                // paramètres de compte
                if ((int)$request->get('editAccount') == 1) {

                    // modification de pseudo
                    $oldPseudo = $user->getOldUsernames();
                    $pseudo = trim((string)$request->get('pseudo'));
                    if (count($oldPseudo)<4 || in_array($pseudo, $oldPseudo)) {
                        $oPseudo = $user->getUsername();
                        if ($oPseudo != $pseudo && !empty($pseudo)) {
                            // le pseudo n'est pas actuellement utilisé
                            if (!$em->getRepository(User::class)->findUserByUsername($pseudo)) {
                                // le pseudo n'est pas utilisé par un autre joueur
                                if (!$em->getRepository(User::class)->findUserByOldPseudo($pseudo, $user->getId())) {
                                    $user->setUsername($pseudo);
                                    $user->addOldUsername($oPseudo);
                                } else {
                                    $this->get('session')->getFlashBag()->add(
                                        'notice',
                                        $translator->trans('notice.pseudoUtilise')
                                    );
                                }
                            } else {
                                $this->get('session')->getFlashBag()->add(
                                    'notice',
                                    $translator->trans('notice.pseudoUtilise')
                                );
                            }
                        }
                    } else {
                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $translator->trans('notice.pseudoMax')
                        );
                    }

                    // modification d'email
                    $email = (string)$request->get('email');
                    if (preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#', $email)) {
                        $oEmail = $user->getEmail();
                        if ($oEmail != $email) {
                            if (!$em->getRepository(User::class)->findUserByEmail($email)) {
                                if (null === $user->getConfirmationToken()) {
                                    $user->setConfirmationToken($this->get('ninja_tooken_user.util.token_generator')->generateToken());
                                }
                                $user->setEmail($email);
                            } else {
                                $this->get('session')->getFlashBag()->add(
                                    'notice',
                                    $translator->trans('notice.mailModifierKo')
                                );
                            }
                        }
                    }

                    $user->setGender((string)$request->get('gender')=='f'?'f':'m');
                    $user->setLocale((string)$request->get('locale')=='fr'?'fr':'en');

                    $user->setDateOfBirth(new \DateTime((int)$request->get('annee')."-".(int)$request->get('mois')."-".(int)$request->get('jour')));

                    $user->setDescription((string)$request->get('user_description'));

                    $user->setReceiveNewsletter((int)$request->get('news') == 1);
                    $user->setReceiveAvertissement((int)$request->get('mail') == 1);

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.parametreModifierOk')
                    );

                    $update = true;
                }

                // permet d'enregistrer les modifications
                if ($update) {
                    $em->persist($user);
                    $em->flush();
                }

            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresUpdateAvatar(Request $request, TranslatorInterface $translator, ParameterBagInterface $params)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            // post request
            if ($request->getMethod() === 'POST') {
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $em = $this->getDoctrine()->getManager();

                // permet de générer le fichier
                $file = $request->files->get('avatar');
                if ($file !== null) {
                    $extension = strtolower($file->guessExtension());
                    if (in_array($extension, array('jpeg','jpg','png','gif'))) {
                        $user->setFile($file);
                        $userWebAvatar = $user->getWebAvatar();
                        if (isset($userWebAvatar) && !empty($userWebAvatar)) {
                            $cachedImage = $params->get('kernel.project_dir') . '/public/cache/avatar/' . $userWebAvatar;
                            if (file_exists($cachedImage)) {
                                unlink($cachedImage);
                            }
                        }
                        $user->setAvatar('update');
                    }
                }

                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.avatarModifierOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresConfirmMail(TranslatorInterface $translator)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $confirmation = $user->getConfirmationToken();
            if (isset($confirmation) && !empty($confirmation)) {

                $this->container->get('ninja_tooken_user.mailer')->sendConfirmationEmailMessage($user);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.mailConfirmationOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresUpdatePassword(Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {

            $form = $this->createForm(ChangePasswordFormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $this->get('security.token_storage')->getToken()->getUser();

                // Encode the plain password, and set it.
                $encodedPassword = $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );

                $user->setPassword($encodedPassword);
                if ($this->getDoctrine()->getManager()->flush()) {

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.motPasseModifierOk')
                    );
                }
            }

            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresDeleteAccount(\App\Listener\ClanPropositionListener $clanPropositionListener, \App\Listener\ThreadListener $threadListener, \App\Listener\CommentListener $commentListener)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $em = $this->getDoctrine()->getManager();
            $conn = $em->getConnection();
            $evm = $em->getEventManager();

            // enlève les évènement sur clan_proposition
            // on évite d'envoyer des messages qui seront supprimés
            $evm->removeEventListener(array('postRemove'), $clanPropositionListener);

            // enlève les évènement sur thread et comment
            // tout sera remis à plat à la fin
            $evm->removeEventListener(array('postRemove'), $threadListener);
            $evm->removeEventListener(array('postRemove'), $commentListener);

            // supprime l'utilisateur
            $em->remove($user);
            $em->flush();

            // recalcul les nombres de réponses d'un thread
            $conn->executeUpdate("UPDATE nt_thread as t LEFT JOIN (SELECT COUNT(nt_comment.id) as num, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.num_comments = isnull(c.num, 0)");
            // recalcul les nombres de réponses d'un forum
            $conn->executeUpdate("UPDATE nt_forum as f LEFT JOIN (SELECT COUNT(nt_thread.id) as num, forum_id FROM nt_thread GROUP BY forum_id) t ON t.forum_id=f.id SET f.num_threads = isnull(t.num, 0)");

            // ré-affecte les derniers commentaires
            $conn->executeUpdate("UPDATE nt_thread as t LEFT JOIN (SELECT MAX(date_ajout) as lastAt, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.last_comment_at = c.lastAt");
            $conn->executeUpdate("UPDATE nt_thread as t LEFT JOIN (SELECT author_id as lastBy, thread_id, date_ajout FROM nt_comment as ct) c ON c.thread_id=t.id and c.date_ajout=t.last_comment_at SET t.lastCommentBy_id = c.lastBy");
            $conn->executeUpdate("UPDATE nt_thread as t SET t.last_comment_at=t.date_ajout WHERE t.last_comment_at IS NULL");

            // supprime l'utilisateur de la session
            $session = new \Symfony\Component\HttpFoundation\Session\Session();
            $session->invalidate();

            return $this->redirect($this->generateUrl('ninja_tooken_homepage'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amis($page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $repo = $this->getDoctrine()->getManager()->getRepository(Friend::class);

            $friends = $repo->getFriends($user, $num, $page);
            $numFriends = $repo->getNumFriends($user);

            return $this->render('user/amis.html.twig', array(
                'friends' => $friends,
                'numFriends' => $numFriends,
                'numBlocked' => $repo->getNumBlocked($user),
                'numDemande' => $repo->getNumDemandes($user),
                'page' => $page,
                'nombrePage' => ceil($numFriends/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisDemande($page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $repo = $this->getDoctrine()->getManager()->getRepository(Friend::class);

            $demandes = $repo->getDemandes($user, $num, $page);

            return $this->render('user/amis.html.twig', array(
                'demandes' => $demandes,
                'numFriends' => $repo->getNumFriends($user),
                'numBlocked' => $repo->getNumBlocked($user),
                'numDemande' => $repo->getNumDemandes($user),
                'page' => $page,
                'nombrePage' => ceil(count($demandes)/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisBlocked($page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);

            $user = $this->get('security.token_storage')->getToken()->getUser();

            $repo = $this->getDoctrine()->getManager()->getRepository(Friend::class);

            $blocked = $repo->getBlocked($user, $num, $page);

            return $this->render('user/amis.html.twig', array(
                'blocked' => $blocked,
                'numFriends' => $repo->getNumFriends($user),
                'numBlocked' => $repo->getNumBlocked($user),
                'numDemande' => $repo->getNumDemandes($user),
                'page' => $page,
                'nombrePage' => ceil(count($blocked)/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("friend", class="App\Entity\User\Friend")
     */
    public function amisConfirmer(TranslatorInterface $translator, Friend $friend)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if ($friend->getUser() == $user) {
                $em = $this->getDoctrine()->getManager();

                $friend->setIsConfirmed(true);
                $em->persist($friend);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.amiAjoutOk', array('%utilisateur%' => $friend->getFriend()->getUsername()))
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("friend", class="App\Entity\User\Friend")
     */
    public function amisBloquer(TranslatorInterface $translator, Friend $friend)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if ($friend->getUser() == $user) {
                $em = $this->getDoctrine()->getManager();

                $friend->setIsBlocked(true);
                $em->persist($friend);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.amiBlockOk', array('%utilisateur%' => $friend->getFriend()->getUsername()))
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("friend", class="App\Entity\User\Friend")
     */
    public function amisDebloquer(TranslatorInterface $translator, Friend $friend)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if ($friend->getUser() == $user) {
                $em = $this->getDoctrine()->getManager();

                $friend->setIsBlocked(false);
                $em->persist($friend);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.amiUnblockOk', array('%utilisateur%' => $friend->getFriend()->getUsername()))
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("friend", class="App\Entity\User\Friend")
     */
    public function amisSupprimer(TranslatorInterface $translator, Friend $friend)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if ($friend->getUser() == $user) {
                $em = $this->getDoctrine()->getManager();

                $em->remove($friend);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.amiSupprimerOk')
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisBlockedSupprimer(TranslatorInterface $translator)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository(Friend::class);

            $repo->deleteAllBlocked($user);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $translator->trans('notice.amiSupprimerAllOk')
            );
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisDemandeSupprimer(TranslatorInterface $translator)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository(Friend::class);

            $repo->deleteAllDemandes($user);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $translator->trans('notice.amiSupprimerAllOk')
            );
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function captures($page)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);

            $captures = $this->getDoctrine()->getManager()
                ->getRepository(Capture::class)
                ->getCaptures($this->get('security.token_storage')->getToken()->getUser(), $num, $page);

            return $this->render('user/captures.html.twig', array(
                'captures' => $captures,
                'page' => $page,
                'nombrePage' => ceil(count($captures)/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("capture", class="App\Entity\User\Capture")
     */
    public function capturesSupprimer(TranslatorInterface $translator, Capture $capture)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            if ($capture->getUser() == $user) {
                // supprime d'imgur
                $imgur = $this->getParameter('imgur');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
                curl_setopt($ch, CURLOPT_URL, "https://api.imgur.com/3/image/".$capture->getDeleteHash());
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID '.$imgur) );
                if ($retour = curl_exec($ch)) {
                    $em = $this->getDoctrine()->getManager();

                    $em->remove($capture);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.captureSupprimerOk')
                    );
                }
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_captures'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function online(User $user) {
        $em = $this->getDoctrine()->getManager();
        $statement = $em->getConnection()->prepare('SELECT userID FROM ajax_chat_online WHERE userID = :userID AND  dateTime > DATE_SUB(NOW(), INTERVAL 10 MINUTE);');
        $statement->bindValue('userID', $user->getId());
        $statement->execute();
        if (!$statement->fetch()) {
            // vérifie en jeu
            $statement = $em->getConnection()->prepare('SELECT user_id FROM nt_lobby_user WHERE user_id = :userID;');
            $statement->bindValue('userID', $user->getId());
            $statement->execute();
            if ($statement->fetch()) {
                return new Response("online");
            }
            return new Response("offline");
        }
        return new Response("online");
    }
}
