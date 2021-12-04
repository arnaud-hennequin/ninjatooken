<?php

namespace App\Controller;

use App\Listener\ClanPropositionListener;
use App\Listener\CommentListener;
use App\Listener\ThreadListener;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
    private CsrfTokenManagerInterface $tokenManager;
    protected ?UserInterface $user;

    public function __construct(CsrfTokenManagerInterface $tokenManager, TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        $this->tokenManager = $tokenManager;
    }

    public function oldUser(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): RedirectResponse
    {
        $user = $em->getRepository('App\Entity\User\User')->findOneBy(array('old_id' => (int)$request->get('ID')));

        if (!$user) {
            throw new NotFoundHttpException($translator->trans('notice.utilisateur.error404'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_fiche', array(
            'user_nom' => $user->getSlug(),
            'page' => 1
        )));
    }

    public function register(Request $request, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // user already exists
            if ($em->getRepository(User::class)->findBy(['emailCanonical' => $user->getEmailCanonical()])) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.mailModifierKo')
                );
                return $this->render('user/registration/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            if ($em->getRepository(User::class)->findBy(['usernameCanonical' => $user->getUsernameCanonical()])) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.pseudoUtilise')
                );
                return $this->render('user/registration/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // encode the plain password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($user);
            $em->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('ninja_tooken_homepage');
        }

        return $this->render('user/registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function login(Request $request): Response
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

    public function connected(User $user, GameData $gameData, EntityManagerInterface $em): Response
    {
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

    public function autologin(Request $request, TranslatorInterface $translator, TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker, $autologin): RedirectResponse
    {
        if (!empty($autologin)) {
            if (!$authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') && !$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
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
                    $request->getSession()->getFlashBag()->add(
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
    public function desinscription(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, ?User $user): RedirectResponse
    {
        if (null !== $user) {
            $user->setReceiveNewsletter(false);
            $user->setReceiveAvertissement(false);
            $em->persist($user);
            $em->flush();

            $request->getSession()->getFlashBag()->add(
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
    public function fiche(User $user, EntityManagerInterface $em, $page = 1): Response
    {
        // amis
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        
        $repo = $em->getRepository(Friend::class);

        $friends = $repo->getFriends($user, $num, $page);

        return $this->render('user/fiche.html.twig', array(
            'friends' => $friends,
            'page' => $page,
            'nombrePage' => ceil($repo->getNumFriends($user)/$num),
            'user' => $user
        ));
    }

    public function messagerieEnvoi(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker, $page=1): Response
    {
        return $this->getMessagerie($request, $translator, $em, $authorizationChecker, $page, false);
    }

    public function messagerie(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker, $page=1): Response
    {
        return $this->getMessagerie($request, $translator, $em, $authorizationChecker, $page);
    }

    public function getMessagerie(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker, $page=1, $reception=true): Response
    {
        if ($authorizationChecker->isGranted('ROLE_USER') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);
            $id = 0;

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
                                $destinataire = $repo_user->findOneBy(['id' => (int)$desti]);
                                if ($destinataire)
                                    $destinataires[] = $destinataire;
                            }
                        }

                        if (!empty($destinataires)) {
                            $message->setAuthor($this->user);
                            foreach($destinataires as $destinataire) {
                                $messageuser = new MessageUser();
                                $messageuser->setDestinataire($destinataire);
                                $message->addReceiver($messageuser);

                                $em->persist($messageuser);
                            }
                            $em->persist($message);

                            $em->flush();
                        }

                        $request->getSession()->getFlashBag()->add(
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
                    $message = current($reception?$repo_message->getFirstReceiveMessage($this->user):$repo_message->getFirstSendMessage($this->user));
                    if ($message)
                        $id = $message->getId();
                }

                if ($message) {
                    // en réception
                    if ($reception) {
                        foreach($message->getReceivers() as $receiver) {
                            if ($receiver->getDestinataire() == $this->user) {
                                // suppression du message
                                if ($isDeleteMessage) {
                                    $receiver->setHasDeleted(true);
                                    $em->persist($receiver);
                                    $em->flush();

                                    $request->getSession()->getFlashBag()->add(
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

                        $request->getSession()->getFlashBag()->add(
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
                $messages = $repo_message->getReceiveMessages($this->user, $num, $page);
                $total = $repo_message->getNumReceiveMessages($this->user);
            } else {
                $messages = $repo_message->getSendMessages($this->user, $num, $page);
                $total = $repo_message->getNumSendMessages($this->user);
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

    public function userFind(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $response = new JsonResponse();
        $users = array();

        if ($request->isXmlHttpRequest()) {
            $user = (string)$request->query->get('q');

            if (!empty($user)) {
                $users = $em
                    ->getRepository(User::class)
                    ->searchUser($user, 10, false);
            }
        }

        $response->setData($users);
        return $response;
    }

    public function parametres(AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            if ($this->user->getDateOfBirth()==new \DateTime('0000-00-00 00:00:00'))
                $this->user->setDateOfBirth(null);

            $form = $this->createForm(ChangePasswordFormType::class);

            return $this->render('user/parametres.html.twig', array(
                'form' => $form->createView()
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresUpdate(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            // post request
            if ($request->getMethod() === 'POST') {
                  $update = false;
                // paramètres de compte
                if ((int)$request->get('editAccount') == 1) {

                    // modification de pseudo
                    $oldPseudo = $this->user->getOldUsernames();
                    $pseudo = trim((string)$request->get('pseudo'));
                    if (count($oldPseudo)<4 || in_array($pseudo, $oldPseudo)) {
                        $oPseudo = $this->user->getUsername();
                        if ($oPseudo != $pseudo && !empty($pseudo)) {
                            // le pseudo n'est pas actuellement utilisé
                            if (!$em->getRepository(User::class)->findUserByUsername($pseudo)) {
                                // le pseudo n'est pas utilisé par un autre joueur
                                if (!$em->getRepository(User::class)->findUserByOldPseudo($pseudo, $this->user->getId())) {
                                    $this->user->setUsername($pseudo);
                                    $this->user->addOldUsername($oPseudo);
                                } else {
                                    $request->getSession()->getFlashBag()->add(
                                        'notice',
                                        $translator->trans('notice.pseudoUtilise')
                                    );
                                }
                            } else {
                                $request->getSession()->getFlashBag()->add(
                                    'notice',
                                    $translator->trans('notice.pseudoUtilise')
                                );
                            }
                        }
                    } else {
                        $request->getSession()->getFlashBag()->add(
                            'notice',
                            $translator->trans('notice.pseudoMax')
                        );
                    }

                    // modification d'email
                    $email = (string)$request->get('email');
                    if (preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#', $email)) {
                        $oEmail = $this->user->getEmail();
                        if ($oEmail != $email) {
                            if (!$em->getRepository(User::class)->findUserByEmail($email)) {
                                if (null === $this->user->getConfirmationToken()) {
                                    $this->user->setConfirmationToken($this->get('ninja_tooken_user.util.token_generator')->generateToken());
                                }
                                $this->user->setEmail($email);
                            } else {
                                $request->getSession()->getFlashBag()->add(
                                    'notice',
                                    $translator->trans('notice.mailModifierKo')
                                );
                            }
                        }
                    }

                    $this->user->setGender((string)$request->get('gender')=='f'?'f':'m');
                    $this->user->setLocale((string)$request->get('locale')=='fr'?'fr':'en');

                    $this->user->setDateOfBirth(new \DateTime((int)$request->get('annee')."-".(int)$request->get('mois')."-".(int)$request->get('jour')));

                    $this->user->setDescription((string)$request->get('user_description'));

                    $this->user->setReceiveNewsletter((int)$request->get('news') == 1);
                    $this->user->setReceiveAvertissement((int)$request->get('mail') == 1);

                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.parametreModifierOk')
                    );

                    $update = true;
                }

                // permet d'enregistrer les modifications
                if ($update) {
                    $em->persist($this->user);
                    $em->flush();
                }

            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresUpdateAvatar(Request $request, TranslatorInterface $translator, ParameterBagInterface $params, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            // post request
            if ($request->getMethod() === 'POST') {
                // permet de générer le fichier
                $file = $request->files->get('avatar');
                if ($file !== null) {
                    $extension = strtolower($file->guessExtension());
                    if (in_array($extension, array('jpeg','jpg','png','gif'))) {
                        $this->user->setFile($file);
                        $userWebAvatar = $this->user->getWebAvatar();
                        if (isset($userWebAvatar) && !empty($userWebAvatar)) {
                            $cachedImage = $params->get('kernel.project_dir') . '/public/cache/avatar/' . $userWebAvatar;
                            if (file_exists($cachedImage)) {
                                unlink($cachedImage);
                            }
                        }
                        $this->user->setAvatar('update');
                    }
                }

                $em->persist($this->user);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.avatarModifierOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresConfirmMail(Request $request, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $confirmation = $this->user->getConfirmationToken();
            if (isset($confirmation) && !empty($confirmation)) {

                $this->container->get('ninja_tooken_user.mailer')->sendConfirmationEmailMessage($this->user);

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.mailConfirmationOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function parametresUpdatePassword(Request $request, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {

            $form = $this->createForm(ChangePasswordFormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Encode the plain password, and set it.
                $encodedPassword = $passwordHasher->hashPassword(
                    $this->user,
                    $form->get('plainPassword')->getData()
                );

                $this->user->setPassword($encodedPassword);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.motPasseModifierOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_user_parametres'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function parametresDeleteAccount(ClanPropositionListener $clanPropositionListener, ThreadListener $threadListener, CommentListener $commentListener, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
             $conn = $em->getConnection();
            $evm = $em->getEventManager();

            // enlève les évènement sur clan_proposition
            // on évite d'envoyer des messages qui seront supprimés
            $evm->removeEventListener(['postRemove'], $clanPropositionListener);

            // enlève les évènement sur thread et comment
            // tout sera remis à plat à la fin
            $evm->removeEventListener(['postRemove'], $threadListener);
            $evm->removeEventListener(['postRemove'], $commentListener);

            // supprime l'utilisateur
            $em->remove($this->user);
            $em->flush();

            // recalcul les nombres de réponses d'un thread
            $conn->executeStatement("UPDATE nt_thread as t LEFT JOIN (SELECT COUNT(nt_comment.id) as num, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.num_comments = ifnull(c.num, 0)");
            // recalcul les nombres de réponses d'un forum
            $conn->executeStatement("UPDATE nt_forum as f LEFT JOIN (SELECT COUNT(nt_thread.id) as num, forum_id FROM nt_thread GROUP BY forum_id) t ON t.forum_id=f.id SET f.num_threads = ifnull(t.num, 0)");

            // ré-affecte les derniers commentaires
            $conn->executeStatement("UPDATE nt_thread as t LEFT JOIN (SELECT MAX(date_ajout) as lastAt, thread_id FROM nt_comment GROUP BY thread_id) c ON c.thread_id=t.id SET t.last_comment_at = c.lastAt");
            $conn->executeStatement("UPDATE nt_thread as t LEFT JOIN (SELECT author_id as lastBy, thread_id, date_ajout FROM nt_comment as ct) c ON c.thread_id=t.id and c.date_ajout=t.last_comment_at SET t.lastCommentBy_id = c.lastBy");
            $conn->executeStatement("UPDATE nt_thread as t SET t.last_comment_at=t.date_ajout WHERE t.last_comment_at IS NULL");

            // supprime l'utilisateur de la session
            $session = new \Symfony\Component\HttpFoundation\Session\Session();
            $session->invalidate();

            return $this->redirect($this->generateUrl('ninja_tooken_homepage'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amis($page, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);
            $repo = $em->getRepository(Friend::class);
            $friends = $repo->getFriends($this->user, $num, $page);
            $numFriends = $repo->getNumFriends($this->user);

            return $this->render('user/amis.html.twig', array(
                'friends' => $friends,
                'numFriends' => $numFriends,
                'numBlocked' => $repo->getNumBlocked($this->user),
                'numDemande' => $repo->getNumDemandes($this->user),
                'page' => $page,
                'nombrePage' => ceil($numFriends/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisDemande($page, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);
            $repo = $em->getRepository(Friend::class);

            $demandes = $repo->getDemandes($this->user, $num, $page);

            return $this->render('user/amis.html.twig', array(
                'demandes' => $demandes,
                'numFriends' => $repo->getNumFriends($this->user),
                'numBlocked' => $repo->getNumBlocked($this->user),
                'numDemande' => $repo->getNumDemandes($this->user),
                'page' => $page,
                'nombrePage' => ceil(count($demandes)/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisBlocked($page, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);
            $repo = $em->getRepository(Friend::class);

            $blocked = $repo->getBlocked($this->user, $num, $page);

            return $this->render('user/amis.html.twig', array(
                'blocked' => $blocked,
                'numFriends' => $repo->getNumFriends($this->user),
                'numBlocked' => $repo->getNumBlocked($this->user),
                'numDemande' => $repo->getNumDemandes($this->user),
                'page' => $page,
                'nombrePage' => ceil(count($blocked)/$num)
            ));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @ParamConverter("friend", class="App\Entity\User\Friend")
     */
    public function amisConfirmer(Request $request, TranslatorInterface $translator, Friend $friend, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            if ($friend->getUser() === $this->user) {
                $friend->setIsConfirmed(true);
                $em->persist($friend);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
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
    public function amisBloquer(Request $request, TranslatorInterface $translator, Friend $friend, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
               if ($friend->getUser() === $this->user) {
                $friend->setIsBlocked(true);
                $em->persist($friend);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
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
    public function amisDebloquer(Request $request, TranslatorInterface $translator, Friend $friend, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            if ($friend->getUser() === $this->user) {
                $friend->setIsBlocked(false);
                $em->persist($friend);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
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
    public function amisSupprimer(Request $request, TranslatorInterface $translator, Friend $friend, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            if ($friend->getUser() === $this->user) {
                $em->remove($friend);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.amiSupprimerOk')
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisBlockedSupprimer(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $repo = $em->getRepository(Friend::class);

            $repo->deleteAllBlocked($this->user);

            $request->getSession()->getFlashBag()->add(
                'notice',
                $translator->trans('notice.amiSupprimerAllOk')
            );
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function amisDemandeSupprimer(Request $request, TranslatorInterface $translator, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $repo = $em->getRepository(Friend::class);

            $repo->deleteAllDemandes($this->user);

            $request->getSession()->getFlashBag()->add(
                'notice',
                $translator->trans('notice.amiSupprimerAllOk')
            );
            return $this->redirect($this->generateUrl('ninja_tooken_user_amis_blocked'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function captures($page, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $num = $this->getParameter('numReponse');
            $page = max(1, $page);

            $captures = $em
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
    public function capturesSupprimer(Request $request, TranslatorInterface $translator, Capture $capture, EntityManagerInterface $em, AuthorizationCheckerInterface $authorizationChecker): RedirectResponse
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            if ($capture->getUser() === $this->user) {
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
                if (curl_exec($ch)) {
                    $em->remove($capture);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.captureSupprimerOk')
                    );
                }
            }
            return $this->redirect($this->generateUrl('ninja_tooken_user_captures'));
        }
        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    public function online(User $user, EntityManagerInterface $em): Response
    {
        // vérifie en jeu
        $statement = $em->getConnection()->prepare('SELECT user_id FROM nt_lobby_user WHERE user_id = :userID;');
        $statement->bindValue('userID', $user->getId());
        $result = $statement->executeQuery();
        if ($result->fetchAllAssociative()) {
            return new Response("online");
        }
        return new Response("offline");
    }
}
