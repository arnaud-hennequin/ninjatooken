<?php

namespace App\Controller;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use App\Entity\Forum\Comment;
use App\Entity\Forum\Thread;
use App\Entity\Forum\Forum;
use App\Entity\Clan\Clan;
use App\Entity\User\User;

class CommonController extends AbstractController
{
    public function addBlocker()
    {
        return new Response('<html><body>add</body></html>');
    }

    public function index()
    {
        $num = $this->getParameter('numReponse');
        $em = $this->getDoctrine()->getManager();

        return $this->render('common/index.html.twig', array(
            'threads' => $em->getRepository(Thread::class)->findBy(
                array('forum' => $em->getRepository(Forum::class)->findOneBy(array('slug' => 'nouveautes'))),
                array('dateAjout' => 'DESC'),
                $num,0
            )
        ));
    }

    public function jouer()
    {
        $response = $this->render('common/jouer.html.twig');
        $response->setSharedMaxAge(600);
        
        return $response;
    }

    public function manuel()
    {
        return $this->render('common/manuel.html.twig');
    }

    public function reglement()
    {
        return $this->render('common/reglement.html.twig');
    }

    public function chat()
    {
        return $this->render('common/chat.html.twig');
    }

    public function faqGenerale()
    {
        return $this->render('common/faqGenerale.html.twig');
    }

    public function faqTechnique()
    {
        return $this->render('common/faqTechnique.html.twig');
    }

    public function team()
    {
        return $this->render('common/team.html.twig');
    }

    public function mentionsLegales()
    {
        return $this->render('common/mentionsLegales.html.twig');
    }

    public function contact(Request $request, TranslatorInterface $translator)
    {
        if ('POST' === $request->getMethod()) {
            $csrfProcsrfTokenManagervider = $this->get('security.csrf.token_manager');
            if(!$csrfTokenManager->isTokenValid(new CsrfToken('contact'.$request->cookies->get('PHPSESSID'), $request->request->get('_token')))) {
                throw new RuntimeException('CSRF attack detected.');
            }
            $texte = trim($request->get('content'));
            $sujet = trim($request->get('sujet'));
            $email = trim($request->get('email'));
            if(!empty($texte)){
                $emailContact = $this->getParameter('mail_admin');

                $message = \Swift_Message::newInstance()
                    ->setSubject('[NT] Contact : '.$sujet)
                    ->setFrom($email)
                    ->setTo($emailContact)
                    ->setContentType("text/html")
                    ->setBody($this->renderView('common/contactEmail.html.twig', array(
                        'texte' => $texte,
                        'email' => $email,
                        'locale' => 'fr'
                    )));

                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.contact')
                );
            }
        }

        return $this->render('common/contact.html.twig');
    }

    public function search(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $num = $this->getParameter('numReponse');
        $q = (string)$request->get('q');

        // recherche dans les threads
        $threads = $em->getRepository(Thread::class)->searchThreads(null, null, $q, $num, 1);

        // recherche dans les commentaires
        $comments = $em->getRepository(Comment::class)->searchComments(null, null, $q, $num, 1);
        foreach($comments as $comment){
            $thread = $comment->getThread();
            $finded = false;
            foreach($threads as $t){
                if($thread == $t){
                    $finded = true;
                    break;
                }
            }
            if(!$finded)
                $threads[] = $thread;
        }

        return $this->render('common/search.html.twig', array(
            'clans' => $em->getRepository(Clan::class)->searchClans($q, $num, 1),
            'users' => $em->getRepository(User::class)->searchUser($q, $num),
            'threads' => $threads,
            'forum' => null
        ));
    }
}
