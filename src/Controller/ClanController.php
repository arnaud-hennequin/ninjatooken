<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Clan\Clan;
use App\ClanBundle\Form\Type\ClanType;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Clan\ClanProposition;
use App\Entity\Clan\ClanPostulation;
use App\Entity\Forum\Forum;
use App\Entity\Forum\Thread;
use App\Entity\User\User;
use App\Entity\User\Message;
use App\Entity\User\MessageUser;

class ClanController extends Controller
{
    public function liste(Request $request, $page = 1)
    {
        $num = $this->container->getParameter('numReponse');
        $page = max(1, $page);

        $em = $this->getDoctrine()->getManager();

        $order = $request->get('order');
        if(empty($order))
            $order = 'composition';

        $repo = $em->getRepository(Clan::class);

        return $this->render('clan/liste.html.twig', array(
            'clans' => $repo->getClans($order, $num, $page),
            'lastClans' => $repo->getClans("date", 10, 1),
            'page' => $page,
            'nombrePage' => ceil($repo->getNumClans()/$num),
            'order' => $order
        ));
    }

    /**
     * @ParamConverter("clan", class="App\Entity\Clan\Clan", options={"mapping": {"clan_nom":"slug"}})
     */
    public function clan(Clan $clan)
    {
        $em = $this->getDoctrine()->getManager();

        // le forum du clan
        $forum = $em->getRepository(Forum::class)->getForum($clan->getSlug(), $clan);
        $threads = array();
        if($forum){
            $forum = current($forum);
            $threads = $em->getRepository(Thread::class)->getThreads($forum, 5, 1);
            if(count($threads)>0)
                $forum->threads = $threads;
            else
                $forum->threads = array();
        }

        // l'arborescence des membres
        $shishou = $em->getRepository(ClanUtilisateur::class)->getMembres($clan, 0, null, 1, 1);
        $membres = array();
        if($shishou){
            $shishou = current($shishou);
            $membres = array(
                'recruteur' => $shishou,
                'recruts' => $this->getRecruts($shishou)
            );
        }

        // l'arborescence des membres mise à plat (listing simple)
        $membresListe = $this->getRecruteur($membres);

        return $this->render('clan/clan.html.twig', array(
            'clan' => $clan,
            'forum' => $forum,
            'membres' => $membres,
            'membresListe' => $membresListe
        ));
    }

    public function clanAjouter(Request $request)
    {
        $authorizationChecker = $this->get('security.authorization_checker');

        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if(!$user->getClan()){
                $clan = new Clan();
                $form = $this->createForm(ClanType::class, $clan);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('clan', array_merge(
                        $request->request->get('clan'),
                        array('description' => $request->get('clan_description'))
                    ));

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();

                        // permet de générer le fichier
                        $file = $request->files->get('clan')['kamonUpload'];
                        if($file !== null){
                            $extension = strtolower($file->guessExtension());
                            if(in_array($extension, array('jpeg','jpg','png','gif'))){
                                $clan->setFile($file);
                                $cachedImage = dirname(__FILE__).'/../../../../public/cache/kamon/'.$clan->getWebKamonUpload();
                                if(file_exists($cachedImage)){
                                    unlink($cachedImage);
                                }
                                $clan->setKamonUpload('upload');
                            }
                        }

                        $clanutilisateur = new ClanUtilisateur();
                        $clanutilisateur->setCanEditClan(true);
                        $clanutilisateur->setRecruteur($user);
                        $clanutilisateur->setMembre($user);

                        $clan->addMembre($clanutilisateur);
                        $user->setClan($clanutilisateur);

                        $forum = new Forum();
                        $forum->setNom($clan->getNom());
                        $forum->setClan($clan);

                        $thread = new Thread();
                        $thread->setNom('Général');
                        $thread->setBody($clan->getDescription());
                        $thread->setForum($forum);
                        $thread->setAuthor($user);

                        $em->persist($thread);
                        $em->persist($forum);
                        $em->persist($clanutilisateur);
                        $em->persist($user);
                        $em->persist($clan);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.clan.ajoutOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                            'clan_nom' => $clan->getSlug()
                        )));
                    }
                }
            }else{
                return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                    'clan_nom' => $user->getClan()->getClan()->getSlug()
                )));
            }
            return $this->render('clan/clan.form.html.twig', array(
                'form' => $form->createView()
            ));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     */
    public function clanEditerSwitch(User $utilisateur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $this->get('security.token_storage')->getToken()->getUser();

            // vérification des droits utilisateurs
            $isShisho = false;
            if($user->getClan()){
                if($user->getClan()->getDroit()==0)
                    $isShisho = true;
            }

            if($isShisho || $authorizationChecker->isGranted('ROLE_ADMIN') !== false || $authorizationChecker->isGranted('ROLE_MODERATOR') !== false){

                $clanutilisateur = $utilisateur->getClan();
                $clan = $user->getClan()->getClan();
                if($clanutilisateur && $clanutilisateur->getClan()==$clan){
                    $em = $this->getDoctrine()->getManager();

                    $clanutilisateur->setCanEditClan(!$clanutilisateur->getCanEditClan());
                    $em->persist($clanutilisateur);

                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $this->get('translator')->trans('notice.clan.editOk')
                    );
                }
                return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                    'clan_nom' => $clan->getSlug()
                )));
            }
            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("clan", class="App\Entity\Clan\Clan", options={"mapping": {"clan_nom":"slug"}})
     */
    public function clanModifier(Request $request, Clan $clan)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            // vérification des droits utilisateurs
            $canEdit = false;
            $clanutilisateur = $user->getClan();
            if($clanutilisateur){
                if($clanutilisateur->getClan() == $clan && ($clanutilisateur->getCanEditClan() || $clanutilisateur->getDroit()==0))
                    $canEdit = true;
            }

            if($canEdit || $authorizationChecker->isGranted('ROLE_ADMIN') !== false || $authorizationChecker->isGranted('ROLE_MODERATOR') !== false){
                $form = $this->createForm(ClanType::class, $clan);
                if('POST' === $request->getMethod()) {
                    // cas particulier du formulaire avec tinymce
                    $request->request->set('clan', array_merge(
                        $request->request->get('clan'),
                        array('description' => $request->get('clan_description'))
                    ));

                    $clanWebKamon = $clan->getWebKamonUpload();

                    $form->bind($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();

                        // permet de générer le fichier
                        $file = $request->files->get('clan')['kamonUpload'];
                        if($file !== null){
                            $extension = strtolower($file->guessExtension());
                            if(in_array($extension, array('jpeg','jpg','png','gif'))){
                                $clan->setFile($file);
                                if(isset($clanWebKamon) && !empty($clanWebKamon)){
                                    $cachedImage = dirname(__FILE__).'/../../../../public/cache/kamon/'.$clanWebKamon;
                                    if(file_exists($cachedImage)){
                                        unlink($cachedImage);
                                    }
                                }
                                $clan->setKamonUpload('upload');
                            }
                        }

                        $em->persist($clan);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.clan.editOk')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                            'clan_nom' => $clan->getSlug()
                        )));
                    }
                }
                return $this->render('clan/clan.form.html.twig', array(
                    'form' => $form->createView(),
                    'clan' => $clan
                ));
            }
            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("clan", class="App\Entity\Clan\Clan", options={"mapping": {"clan_nom":"slug"}})
     */
    public function clanSupprimer(Clan $clan)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();

            // vérification des droits utilisateurs
            $canDelete = false;
            $clanutilisateur = $user->getClan();
            if($clanutilisateur){
                if($clanutilisateur->getClan() == $clan && $clanutilisateur->getDroit()==0)
                    $canDelete = true;
            }

            if($canDelete || $authorizationChecker->isGranted('ROLE_ADMIN') !== false || $authorizationChecker->isGranted('ROLE_MODERATOR') !== false){
                $em = $this->getDoctrine()->getManager();

                // enlève les évènement sur clan_utilisateur
                // on cherche à tous les supprimer et pas à ré-agencer la structure
                $evm = $em->getEventManager();
                $evm->removeEventListener(array('postRemove'), $this->get('ninjatooken_clan.clan_utilisateur_listener'));

                $em->remove($clan);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans('notice.clan.deleteOk')
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     */
    public function clanUtilisateurSupprimer(User $utilisateur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            $userRecruts = $user->getRecruts();
            $clanutilisateur = $utilisateur->getClan();
            if($clanutilisateur){
                // l'utilisateur actuel est le recruteur du joueur visé, ou est le joueur lui-même !
                if( (!empty($userRecruts) && $userRecruts->contains($clanutilisateur)) || $user==$utilisateur ){
                    $clan = $clanutilisateur->getClan();

                    $evm = $em->getEventManager();

                    $membres = $clan->getMembres()->count() - 1;
                    if($membres==0){
                        $evm->removeEventListener(array('postRemove'), $this->get('ninjatooken_clan.clan_utilisateur_listener'));
                        $em->remove($clan);
                    }else{
                        // enlève les évènement sur clan_proposition
                        $evm->removeEventListener(array('postRemove'), $this->get('ninjatooken_clan.clan_proposition_listener'));
                        $em->remove($clanutilisateur);
                    }
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $this->get('translator')->trans('notice.clan.revokeOk')
                    );

                    if($clan && $membres>0)
                        return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                            'clan_nom' => $clan->getSlug()
                        )));
                    else
                        return $this->redirect($this->generateUrl('ninja_tooken_clans'));
                }
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('notice.clan.revokeKo')
            );
            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     */
    public function clanUtilisateurSupprimerShishou(User $utilisateur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            if($user->getClan()){
                $clanutilisateur = $user->getClan();
                // est le shishou
                if($clanutilisateur->getDroit() == 0){
                    $clan = $clanutilisateur->getClan();

                    // on vérifie que le joueur visé fait parti du même clan
                    if($utilisateur->getClan()){
                        $clanutilisateur_promote = $utilisateur->getClan();
                        if($clanutilisateur_promote->getClan() == $clan){

                            // permet de remplacer le ninja promu dans la hiérarchie via le listener
                            $em->remove($clanutilisateur_promote);
                            $em->flush();

                            // modifie la liaison du shisho pour pointer vers le nouveau !
                            $clanutilisateur->setMembre($utilisateur);
                            $em->persist($clanutilisateur);
                            $em->persist($utilisateur);

                            // échange les recruts avec le shishou actuel
                            $recruts = $user->getRecruts();
                            foreach($recruts as $recrut){
                                $recrut->setRecruteur($utilisateur);
                                $em->persist($recrut);
                                $em->persist($utilisateur);
                            }
                            $em->flush();

                            $this->get('session')->getFlashBag()->add(
                                'notice',
                                $this->get('translator')->trans('notice.clan.promotionOk')
                            );

                            return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                                'clan_nom' => $clan->getSlug()
                            )));
                        }
                    }
                }
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('notice.clan.promotionKo')
            );
            return $this->redirect($this->generateUrl('ninja_tooken_clans'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    public function clanUtilisateurRecruter()
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $clan = $user->getClan();
            $em = $this->getDoctrine()->getManager();

            $repo_proposition = $em->getRepository(ClanProposition::class);
            $repo_demande = $em->getRepository(ClanPostulation::class);

            return $this->render('clan/clan.recrutement.html.twig', array(
                'recrutements' => $repo_proposition->getPropositionByRecruteur($user),
                'propositions' => $repo_proposition->getPropositionByPostulant($user),
                'demandes' => $repo_demande->getByUser($user),
                'demandesFrom' => $clan && $clan->getDroit()<3?$repo_demande->getByClan($clan->getClan()):null
            ));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     */
    public function clanUtilisateurRecruterSupprimer(User $utilisateur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            if($user->getClan()){
                $clanProposition = $em->getRepository(ClanProposition::class)->getPropositionByUsers($user, $utilisateur);
                if($clanProposition){
                    $em->remove($clanProposition);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $this->get('translator')->trans('notice.recrutement.cancelOk')
                    );
                }
                return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
            }
            return $this->redirect($this->generateUrl('ninja_tooken_homepage'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     */
    public function clanUtilisateurRecruterAjouter(Request $request, User $utilisateur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            if($user->getClan()){
                $clanProposition = $em->getRepository(ClanProposition::class)->getPropositionByUsers($user, $utilisateur);
                $translator = $this->get('translator');
                if(!$clanProposition){

                    $clanProposition = new ClanProposition();
                    $clanProposition->setRecruteur($user);
                    $clanProposition->setPostulant($utilisateur);
                    // ajoute le message
                    $message = new Message();
                    $message->setAuthor($user);
                    $message->setNom($translator->trans('mail.recrutement.nouveau.sujet'));
                    $message->setContent($translator->trans('mail.recrutement.nouveau.contenu', array(
                        '%userUrl%' => $this->generateUrl('ninja_tooken_user_fiche', array(
                            'user_nom' => $user->getSlug()
                        )),
                        '%userPseudo%' => $user->getUsername(),
                        '%urlRefuser%' => $this->generateUrl('ninja_tooken_clan_recruter_refuser', array(
                            'user_nom' => $utilisateur->getSlug(),
                            'recruteur_nom' => $user->getSlug()
                        )),
                        '%urlAccepter%' => $this->generateUrl('ninja_tooken_clan_recruter_accepter', array(
                            'user_nom' => $utilisateur->getSlug(),
                            'recruteur_nom' => $user->getSlug()
                        ))
                    )));

                    $messageuser = new MessageUser();
                    $messageuser->setDestinataire($utilisateur);
                    $message->addReceiver($messageuser);

                    $em->persist($messageuser);
                    $em->persist($message);
                    $em->persist($clanProposition);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.recrutement.addOk')
                    );
                }else{
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.recrutement.addKo')
                    );
                }
                return $this->redirect($request->headers->get('referer'));
            }
            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     * @ParamConverter("recruteur", class="App\Entity\User\User", options={"mapping": {"recruteur_nom":"slug"}})
     */
    public function clanUtilisateurRecruterAccepter(User $utilisateur, User $recruteur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            $clanProposition = $em->getRepository(ClanProposition::class)->getWaitingPropositionByUsers($recruteur, $utilisateur);
            if($clanProposition){
                if($user == $utilisateur && $recruteur->getClan() !== null){
                    $clanutilisateur = $recruteur->getClan();
                    if($clanutilisateur->getDroit()<3){
                        $translator = $this->get('translator');

                        // on supprime l'ancienne liaison
                        $cu = $user->getClan();
                        if($cu !== null){
                            $user->setClan(null);
                            $em->persist($user);
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
                        $cu->setMembre($user);
                        $cu->setClan($clan);
                        $cu->setDroit($clanutilisateur->getDroit() + 1);
                        $user->setClan($cu);

                        $em->persist($user);
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

                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $translator->trans('notice.recrutement.bienvenue')
                        );

                        return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                            'clan_nom' => $clan->getSlug()
                        )));
                    }
                }
            }
            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("utilisateur", class="App\Entity\User\User", options={"mapping": {"user_nom":"slug"}})
     * @ParamConverter("recruteur", class="App\Entity\User\User", options={"mapping": {"recruteur_nom":"slug"}})
     */
    public function clanUtilisateurRecruterRefuser(User $utilisateur, User $recruteur)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            $clanProposition = $em->getRepository(ClanProposition::class)->getWaitingPropositionByUsers($recruteur, $utilisateur);
            if($clanProposition){
                if($user == $utilisateur){
                    $translator = $this->get('translator');

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
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("clan", class="App\Entity\Clan\Clan", options={"mapping": {"clan_nom":"slug"}})
     */
    public function clanUtilisateurPostuler(Clan $clan)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            // vérification des droits utilisateurs
            $canPostule = true;
            if($user->getClan()){
                $clanUser = $user->getClan()->getClan();
                if($clanUser == $clan)
                    $canPostule = false;
            }

            // le clan recrute, on peut postuler
            if($clan->getIsRecruting() && $canPostule){

                $ok = false;

                $postulation = $em->getRepository(ClanPostulation::class)->getByClanUser($clan, $user);
                if($postulation){
                    // si on avait supprimé la proposition
                    if($postulation->getEtat()==1){
                        if($postulation->getDateChangementEtat() <= new \DateTime('-1 days')){
                            $postulation->setEtat(0);
                            $ok = true;
                        }else
                            $this->get('session')->getFlashBag()->add(
                                'notice',
                                $this->get('translator')->trans('notice.clan.postulationKo2')
                            );
                    }else
                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            $this->get('translator')->trans('notice.clan.postulationKo1')
                        );
                }else{
                    $postulation = new ClanPostulation();
                    $postulation->setClan($clan);
                    $postulation->setPostulant($user);
                    $ok = true;
                }

                if($ok){
                    $em->persist($postulation);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $this->get('translator')->trans('notice.clan.postulationOk')
                    );
                }

            }

            return $this->redirect($this->generateUrl('ninja_tooken_clan', array(
                'clan_nom' => $clan->getSlug()
            )));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    /**
     * @ParamConverter("clan", class="App\Entity\Clan\Clan", options={"mapping": {"clan_nom":"slug"}})
     */
    public function clanUtilisateurPostulerSupprimer(Clan $clan)
    {
        $authorizationChecker = $this->get('security.authorization_checker');
        if($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            $postulation = $em->getRepository(ClanPostulation::class)->getByClanUser($clan, $user);
            if($postulation && $postulation->getEtat()==0){
                $postulation->setEtat(1);
                $em->persist($postulation);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans('notice.clan.postulationSupprimeOk')
                );
            }
            return $this->redirect($this->generateUrl('ninja_tooken_clan_recruter'));
        }
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }

    function getRecruteur($list=array()){
        $membre = array();
        if(isset($list['recruteur'])){
            $membre[] = $list['recruteur'];
            foreach($list['recruts'] as $recrut){
                $membre = array_merge($membre, $this->getRecruteur($recrut));
            }
        }
        return $membre;
    }

    function getRecruts(ClanUtilisateur $recruteur){
        $em = $this->getDoctrine()->getManager();
        $recruts = $em->getRepository(ClanUtilisateur::class)->getMembres(null, null, $recruteur->getMembre(), 100);
        $membres = array();
        foreach($recruts as $recrut){
            $membres[] = array(
                'recruteur' => $recrut,
                'recruts' => $this->getRecruts($recrut)
            );
        }

        return $membres;
    }
}
