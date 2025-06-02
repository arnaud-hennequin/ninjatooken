<?php

namespace App\Controller;

use App\Entity\Clan\Clan;
use App\Entity\Clan\ClanPostulation;
use App\Entity\Clan\ClanProposition;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Forum\Forum;
use App\Entity\Forum\Thread;
use App\Entity\User\Message;
use App\Entity\User\MessageUser;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Form\Type\ClanType;
use App\Listener\ClanPropositionListener;
use App\Repository\ClanPostulationRepository;
use App\Repository\ClanPropositionRepository;
use App\Repository\ClanRepository;
use App\Repository\ClanUtilisateurRepository;
use App\Repository\ForumRepository;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClanController extends AbstractController
{
    protected ?FlashBagInterface $flashBag;
    protected UserInterface|SluggableInterface|null $user;
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

    #[Route('/{_locale}/clan/{page}', name: 'ninja_tooken_clans', requirements: ['page' => '\d*'], defaults: ['page' => 1], methods: ['GET'])]
    public function liste(Request $request, EntityManagerInterface $em, int $page = 1): Response
    {
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        $order = $request->get('order');
        if (empty($order)) {
            $order = 'composition';
        }

        /** @var ClanRepository $repo */
        $repo = $em->getRepository(Clan::class);

        return $this->render('clan/liste.html.twig', [
            'clans' => $repo->getClans($order, $num, $page),
            'lastClans' => $repo->getClans('date', 10, 1),
            'page' => $page,
            'nombrePage' => ceil($repo->getNumClans() / $num),
            'order' => $order,
        ]);
    }

    #[Route('/{_locale}/clan/{clan_nom}', name: 'ninja_tooken_clan')]
    public function clan(Clan $clan, EntityManagerInterface $em): Response
    {
        // le forum du clan
        /** @var ForumRepository $forumRepository */
        $forumRepository = $em->getRepository(Forum::class);
        $forum = $forumRepository->getForum($clan->getSlug(), $clan);
        if ($forum) {
            $forum = current($forum);
            /** @var ThreadRepository $threadRepository */
            $threadRepository = $em->getRepository(Thread::class);
            $threads = $threadRepository->getThreads($forum, 5, 1);
            $forum->setNumThreads(count($threads));
        }

        // l'arborescence des membres
        /** @var ClanUtilisateurRepository $clanUtilisateurRepository */
        $clanUtilisateurRepository = $em->getRepository(ClanUtilisateur::class);
        $shishou = $clanUtilisateurRepository->getMembres($clan, 0, null, 1, 1);
        if ($shishou) {
            $shishou = current($shishou);
            $membres = [
                'recruteur' => $shishou,
                'recruts' => $this->getRecruts($shishou, $em),
            ];
            // l'arborescence des membres mise à plat (listing simple)
            $membresListe = $this->getAllMembers($membres);
        } else {
            $membres = [];
            $membresListe = [];
        }

        return $this->render('clan/clan.html.twig', [
            'clan' => $clan,
            'forum' => $forum,
            'membres' => $membres,
            'membresListe' => $membresListe,
        ]);
    }

    #[Route('/{_locale}/clan-ajouter', name: 'ninja_tooken_clan_ajouter')]
    public function clanAjouter(Request $request, TranslatorInterface $translator, ParameterBagInterface $params, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if (!$this->user->getClan()) {
                $clan = new Clan();
                $form = $this->createForm(ClanType::class, $clan);
                if ('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('clan', array_merge(
                        (array) $request->request->get('clan'),
                        ['description' => $request->get('clan_description')]
                    ));

                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        // permet de générer le fichier
                        $file = $request->files->get('clan');
                        if ($file && isset($file['kamonUpload'])) {
                            $file = $file['kamonUpload'];
                            $extension = strtolower($file->guessExtension());
                            if (in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
                                $clan->setFile($file);
                                $cachedImage = $params->get('kernel.project_dir').'/public/cache/kamon/'.$clan->getWebKamon();
                                if (file_exists($cachedImage)) {
                                    unlink($cachedImage);
                                }
                                $clan->setKamonUpload('upload');
                            }
                        }

                        $clanutilisateur = new ClanUtilisateur();
                        $clanutilisateur->setCanEditClan(true);
                        $clanutilisateur->setRecruteur($this->user);
                        $clanutilisateur->setMembre($this->user);

                        $clan->addMembre($clanutilisateur);
                        $this->user->setClan($clanutilisateur);

                        $forum = new Forum();
                        $forum->setNom($clan->getNom());
                        $forum->setClan($clan);

                        $thread = new Thread();
                        $thread->setNom('['.$clan->getNom().'] - Général');
                        $thread->setBody($clan->getDescription());
                        $thread->setForum($forum);
                        $thread->setAuthor($this->user);

                        $em->persist($thread);
                        $em->persist($forum);
                        $em->persist($clanutilisateur);
                        $em->persist($this->user);
                        $em->persist($clan);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.clan.ajoutOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                            'clan_nom' => $clan->getSlug(),
                        ]));
                    }
                }
            } else {
                return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                    'clan_nom' => $this->user->getClan()->getClan()->getSlug(),
                ]));
            }

            return $this->render('clan/clan.form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan-editer-switch/{user_nom}', name: 'ninja_tooken_clan_editer_switch')]
    public function clanEditerSwitch(TranslatorInterface $translator, User $utilisateur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // vérification des droits utilisateurs
            $isShisho = false;
            if ($this->user->getClan()) {
                if (0 == $this->user->getClan()->getDroit()) {
                    $isShisho = true;
                }
            }

            if ($isShisho || false !== $this->authorizationChecker->isGranted('ROLE_ADMIN') || false !== $this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
                $clanutilisateur = $utilisateur->getClan();
                $clan = $this->user->getClan()->getClan();
                if ($clanutilisateur && $clanutilisateur->getClan() == $clan) {
                    $clanutilisateur->setCanEditClan(!$clanutilisateur->getCanEditClan());
                    $em->persist($clanutilisateur);

                    $em->flush();

                    $this->flashBag?->add(
                        'notice',
                        $translator->trans('notice.clan.editOk')
                    );
                }

                return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                    'clan_nom' => $clan->getSlug(),
                ]));
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan/{clan_nom}/modifier', name: 'ninja_tooken_clan_modifier')]
    public function clanModifier(Request $request, TranslatorInterface $translator, ParameterBagInterface $params, Clan $clan, EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // vérification des droits utilisateurs
            $canEdit = false;
            $clanutilisateur = $this->user->getClan();
            if ($clanutilisateur) {
                if ($clanutilisateur->getClan() == $clan && ($clanutilisateur->getCanEditClan() || 0 == $clanutilisateur->getDroit())) {
                    $canEdit = true;
                }
            }

            if ($canEdit || false !== $this->authorizationChecker->isGranted('ROLE_ADMIN') || false !== $this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
                $form = $this->createForm(ClanType::class, $clan);
                if ('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('clan', array_merge(
                        (array) $request->request->get('clan'),
                        ['description' => $request->get('clan_description')]
                    ));

                    $clanWebKamon = $clan->getWebKamon();
                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        // permet de générer le fichier
                        $file = $request->files->get('clan');
                        if (null !== $file && isset($file['kamonUpload'])) {
                            $file = $file['kamonUpload'];
                            $extension = strtolower($file->guessExtension());
                            if (in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
                                $clan->setFile($file);
                                if (isset($clanWebKamon) && !empty($clanWebKamon)) {
                                    $cachedImage = $params->get('kernel.project_dir').'/public/cache/kamon/'.$clanWebKamon;
                                    if (file_exists($cachedImage)) {
                                        unlink($cachedImage);
                                    }
                                }
                            }
                        }

                        $em->persist($clan);
                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.clan.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                            'clan_nom' => $clan->getSlug(),
                        ]));
                    }
                }

                return $this->render('clan/clan.form.html.twig', [
                    'form' => $form->createView(),
                    'clan' => $clan,
                ]);
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan/{clan_nom}/supprimer', name: 'ninja_tooken_clan_supprimer')]
    public function clanSupprimer(TranslatorInterface $translator, Clan $clan, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // vérification des droits utilisateurs
            $clanutilisateur = $this->user->getClan();
            $canDelete = $clanutilisateur && $clanutilisateur->getClan() == $clan && 0 == $clanutilisateur->getDroit();

            if ($canDelete || false !== $this->authorizationChecker->isGranted('ROLE_ADMIN') || false !== $this->authorizationChecker->isGranted('ROLE_MODERATOR')) {
                // enlève les évènement sur clan_utilisateur
                // on cherche à tous les supprimer et pas à ré-agencer la structure
                $clan->delete = true;

                $em->remove($clan);
                $em->flush();

                $this->flashBag?->add(
                    'notice',
                    $translator->trans('notice.clan.deleteOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan-destituer/{user_nom}', name: 'ninja_tooken_clan_destituer')]
    public function clanUtilisateurSupprimer(TranslatorInterface $translator, ClanPropositionListener $clanPropositionListener, ClanPropositionListener $clanUtilisateurListener, User $utilisateur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $userRecruts = $this->user->getRecruts();
            $clanutilisateur = $utilisateur->getClan();
            if ($clanutilisateur) {
                // l'utilisateur actuel est le recruteur du joueur visé, ou est le joueur lui-même !
                if ((!empty($userRecruts) && $userRecruts->contains($clanutilisateur)) || $this->user === $utilisateur) {
                    $clan = $clanutilisateur->getClan();

                    $evm = $em->getEventManager();

                    $membres = $clan->getMembres()->count() - 1;
                    if (0 == $membres) {
                        $evm->removeEventListener(['postRemove'], $clanUtilisateurListener);
                        $em->remove($clan);
                    } else {
                        // enlève les évènement sur clan_proposition
                        $evm->removeEventListener(['postRemove'], $clanPropositionListener);
                        $em->remove($clanutilisateur);
                    }
                    $em->flush();

                    $this->flashBag?->add(
                        'notice',
                        $translator->trans('notice.clan.revokeOk')
                    );

                    if (null !== $clan && $membres > 0) {
                        return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                            'clan_nom' => $clan->getSlug(),
                        ]));
                    }

                    return $this->redirect($this->generateUrl('ninja_tooken_clans'));
                }
            }
            $this->flashBag?->add(
                'notice',
                $translator->trans('notice.clan.revokeKo')
            );

            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan-destituer-shishou/{user_nom}', name: 'ninja_tooken_clan_destituer_shishou')]
    public function clanUtilisateurSupprimerShishou(TranslatorInterface $translator, User $utilisateur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->user->getClan()) {
                $clanutilisateur = $this->user->getClan();
                // est le shishou
                if (0 == $clanutilisateur->getDroit()) {
                    $clan = $clanutilisateur->getClan();

                    // on vérifie que le joueur visé fait parti du même clan
                    if ($utilisateur->getClan()) {
                        $clanutilisateur_promote = $utilisateur->getClan();
                        if ($clanutilisateur_promote->getClan() == $clan) {
                            // permet de remplacer le ninja promu dans la hiérarchie via le listener
                            $em->remove($clanutilisateur_promote);
                            $em->flush();

                            // modifie la liaison du shisho pour pointer vers le nouveau !
                            $clanutilisateur->setMembre($utilisateur);
                            $em->persist($clanutilisateur);
                            $em->persist($utilisateur);

                            // échange les recruts avec le shishou actuel
                            $recruts = $this->user->getRecruts();
                            foreach ($recruts as $recrut) {
                                $recrut->setRecruteur($utilisateur);
                                $em->persist($recrut);
                                $em->persist($utilisateur);
                            }
                            $em->flush();

                            $this->flashBag?->add(
                                'notice',
                                $translator->trans('notice.clan.promotionOk')
                            );

                            return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                                'clan_nom' => $clan->getSlug(),
                            ]));
                        }
                    }
                }
            }
            $this->flashBag?->add(
                'notice',
                $translator->trans('notice.clan.promotionKo')
            );

            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/compte/clan', name: 'ninja_tooken_clan_recruter')]
    public function clanUtilisateurRecruter(EntityManagerInterface $em): Response
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $clan = $this->user->getClan();

            /** @var ClanPropositionRepository $repo_proposition */
            $repo_proposition = $em->getRepository(ClanProposition::class);
            /** @var ClanPostulationRepository $repo_demande */
            $repo_demande = $em->getRepository(ClanPostulation::class);

            return $this->render('clan/clan.recrutement.html.twig', [
                'recrutements' => $repo_proposition->getPropositionByRecruteur($this->user),
                'propositions' => $repo_proposition->getPropositionByPostulant($this->user),
                'demandes' => $repo_demande->getByUser($this->user),
                'demandesFrom' => $clan && $clan->getDroit() < 3 ? $repo_demande->getByClan($clan->getClan()) : null,
            ]);
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/compte/clan/{user_nom}/supprimer', name: 'ninja_tooken_clan_recruter_supprimer')]
    public function clanUtilisateurRecruterSupprimer(TranslatorInterface $translator, User $utilisateur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->user->getClan()) {
                /** @var ClanPropositionRepository $repo */
                $repo = $em->getRepository(ClanProposition::class);
                $clanProposition = $repo->getPropositionByUsers($this->user, $utilisateur);
                if ($clanProposition) {
                    $em->remove($clanProposition);
                    $em->flush();

                    $this->flashBag?->add(
                        'notice',
                        $translator->trans('notice.recrutement.cancelOk')
                    );
                }

                return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
            }

            return $this->redirect($this->generateUrl('ninja_tooken_homepage'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/compte/clan/{user_nom}', name: 'ninja_tooken_clan_recruter_ajouter')]
    public function clanUtilisateurRecruterAjouter(Request $request, TranslatorInterface $translator, User $utilisateur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            if ($this->user->getClan()) {
                /** @var ClanPropositionRepository $repo */
                $repo = $em->getRepository(ClanProposition::class);
                $clanProposition = $repo->getPropositionByUsers($this->user, $utilisateur);
                if (!$clanProposition) {
                    $clanProposition = new ClanProposition();
                    $clanProposition->setRecruteur($this->user);
                    $clanProposition->setPostulant($utilisateur);
                    // ajoute le message
                    $message = new Message();
                    $message->setAuthor($this->user);
                    $message->setNom($translator->trans('mail.recrutement.nouveau.sujet'));
                    $message->setContent($translator->trans('mail.recrutement.nouveau.contenu', [
                        '%userUrl%' => $this->generateUrl('ninja_tooken_user_fiche', [
                            'user_nom' => $this->user->getSlug(),
                        ]),
                        '%userPseudo%' => $this->user->getUsername(),
                        '%urlRefuser%' => $this->generateUrl('ninja_tooken_clan_recruter_refuser', [
                            'user_nom' => $utilisateur->getSlug(),
                            'recruteur_nom' => $this->user->getSlug(),
                        ]),
                        '%urlAccepter%' => $this->generateUrl('ninja_tooken_clan_recruter_accepter', [
                            'user_nom' => $utilisateur->getSlug(),
                            'recruteur_nom' => $this->user->getSlug(),
                        ]),
                    ]));

                    $messageuser = new MessageUser();
                    $messageuser->setDestinataire($utilisateur);
                    $message->addReceiver($messageuser);

                    $em->persist($messageuser);
                    $em->persist($message);
                    $em->persist($clanProposition);
                    $em->flush();

                    $this->flashBag?->add(
                        'notice',
                        $translator->trans('notice.recrutement.addOk')
                    );
                } else {
                    $this->flashBag?->add(
                        'notice',
                        $translator->trans('notice.recrutement.addKo')
                    );
                }

                return $this->redirect($request->headers->get('referer'));
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/compte/clan/accepter/{user_nom}/{recruteur_nom}', name: 'ninja_tooken_clan_recruter_accepter')]
    public function clanUtilisateurRecruterAccepter(TranslatorInterface $translator, User $utilisateur, User $recruteur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var ClanPropositionRepository $repo */
            $repo = $em->getRepository(ClanProposition::class);
            $clanProposition = $repo->getWaitingPropositionByUsers($recruteur, $utilisateur);
            if ($clanProposition) {
                if ($this->user === $utilisateur && null !== $recruteur->getClan()) {
                    $clanutilisateur = $recruteur->getClan();
                    if ($clanutilisateur->getDroit() < 3) {
                        // on supprime l'ancienne liaison
                        $cu = $this->user->getClan();
                        if (null !== $cu) {
                            $this->user->setClan(null);
                            $em->persist($this->user);
                            $em->remove($cu);
                            $em->flush();
                        }

                        // le nouveau clan
                        $clan = $clanutilisateur->getClan();

                        // on met à jour la proposition
                        $clanProposition->setEtat(1);
                        $em->persist($clanProposition);

                        // on ajoute la nouvelle liaison
                        $cu = new ClanUtilisateur();

                        $cu->setRecruteur($recruteur);
                        $cu->setMembre($this->user);
                        $cu->setClan($clan);
                        $cu->setDroit($clanutilisateur->getDroit() + 1);
                        $this->user->setClan($cu);

                        $em->persist($this->user);
                        $em->persist($cu);

                        // on ajoute un message
                        $message = new Message();
                        $message->setAuthor($utilisateur);
                        $message->setNom($translator->trans('mail.recrutement.accepter.sujet'));
                        $message->setContent($translator->trans('mail.recrutement.accepter.contenu'));
                        $messageuser = new MessageUser();
                        $messageuser->setDestinataire($recruteur);
                        $message->addReceiver($messageuser);
                        $em->persist($messageuser);
                        $em->persist($message);

                        $em->flush();

                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.recrutement.bienvenue')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                            'clan_nom' => $clan->getSlug(),
                        ]));
                    }
                }
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/compte/clan/refuser/{user_nom}/{recruteur_nom}', name: 'ninja_tooken_clan_recruter_refuser')]
    public function clanUtilisateurRecruterRefuser(TranslatorInterface $translator, User $utilisateur, User $recruteur, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var ClanPropositionRepository $repo */
            $repo = $em->getRepository(ClanProposition::class);
            $clanProposition = $repo->getWaitingPropositionByUsers($recruteur, $utilisateur);
            if ($clanProposition) {
                if ($this->user === $utilisateur) {
                    // on met à jour la proposition
                    $clanProposition->setEtat(2);
                    $em->persist($clanProposition);

                    // on ajoute un message
                    $message = new Message();
                    $message->setAuthor($utilisateur);
                    $message->setNom($translator->trans('mail.recrutement.refuser.sujet'));
                    $message->setContent($translator->trans('mail.recrutement.refuser.contenu'));
                    $messageuser = new MessageUser();
                    $messageuser->setDestinataire($recruteur);
                    $message->addReceiver($messageuser);
                    $em->persist($messageuser);
                    $em->persist($message);

                    $em->flush();
                }
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan-postuler/{clan_nom}', name: 'ninja_tooken_clan_postuler')]
    public function clanUtilisateurPostuler(TranslatorInterface $translator, Clan $clan, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // vérification des droits utilisateurs
            $canPostule = true;
            if ($this->user->getClan()) {
                $clanUser = $this->user->getClan()->getClan();
                if ($clanUser == $clan) {
                    $canPostule = false;
                }
            }

            // si c'était hier, on reset la limitation
            if ($this->user->getDateApplication() < new \DateTime('today')) {
                $this->user->setDateApplication(new \DateTime());
                $this->user->setNumberApplication(0);
            }

            $canPostule &= $this->user->getNumberApplication() < User::MAX_APPLICATION_BY_DAY;

            // le clan recrute, on peut postuler
            if ($clan->getIsRecruting() && $canPostule) {
                $ok = false;

                /** @var ClanPostulationRepository $repo */
                $repo = $em->getRepository(ClanPostulation::class);
                $postulation = $repo->getByClanUser($clan, $this->user);
                if ($postulation) {
                    // si on avait supprimé la proposition
                    if (1 == $postulation->getEtat()) {
                        if ($postulation->getDateChangementEtat() <= new \DateTime('-1 days')) {
                            $postulation->setEtat(0);
                            $ok = true;
                        } else {
                            $this->flashBag?->add(
                                'notice',
                                $translator->trans('notice.clan.postulationKo2')
                            );
                        }
                    } else {
                        $this->flashBag?->add(
                            'notice',
                            $translator->trans('notice.clan.postulationKo1')
                        );
                    }
                } else {
                    $postulation = new ClanPostulation();
                    $postulation->setClan($clan);
                    $postulation->setPostulant($this->user);
                    $ok = true;
                }

                if ($ok) {
                    $this->user->setNumberApplication($this->user->getNumberApplication() + 1);

                    $em->persist($this->user);
                    $em->persist($postulation);
                    $em->flush();

                    $this->flashBag?->add(
                        'notice',
                        $translator->trans('notice.clan.postulationOk')
                    );
                }
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clan', [
                'clan_nom' => $clan->getSlug(),
            ]));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    #[Route('/{_locale}/clan-postuler-supprimer/{clan_nom}', name: 'ninja_tooken_clan_postuler_supprimer')]
    public function clanUtilisateurPostulerSupprimer(TranslatorInterface $translator, Clan $clan, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var ClanPostulationRepository $repo */
            $repo = $em->getRepository(ClanPostulation::class);
            $postulation = $repo->getByClanUser($clan, $this->user);
            if ($postulation && 0 == $postulation->getEtat()) {
                $postulation->setEtat(1);
                $em->persist($postulation);
                $em->flush();

                $this->flashBag?->add(
                    'notice',
                    $translator->trans('notice.clan.postulationSupprimeOk')
                );
            }

            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }

        return $this->redirect($this->generateUrl('ninja_tooken_user_security_login'));
    }

    /**
     * @param array{'recruteur': ClanUtilisateur, 'recruts' : array<int, mixed>} $list
     *
     * @return array<int, ClanUtilisateur>
     */
    public function getAllMembers(array $list): array
    {
        $membre = [
            $list['recruteur'],
        ];
        foreach ($list['recruts'] as $recrut) {
            $membre = array_merge($membre, $this->getAllMembers($recrut));
        }

        return $membre;
    }

    /**
     * @return array<int, mixed>
     */
    public function getRecruts(ClanUtilisateur $recruteur, EntityManagerInterface $em): array
    {
        /** @var ClanUtilisateurRepository $repo */
        $repo = $em->getRepository(ClanUtilisateur::class);
        $recruts = $repo->getMembres(null, null, $recruteur->getMembre(), 100);
        $membres = [];
        foreach ($recruts as $recrut) {
            $membres[] = [
                'recruteur' => $recrut,
                'recruts' => $this->getRecruts($recrut, $em),
            ];
        }

        return $membres;
    }
}
