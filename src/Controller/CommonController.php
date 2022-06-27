<?php

namespace App\Controller;

use App\Repository\ClanRepository;
use App\Repository\CommentRepository;
use App\Repository\ForumRepository;
use App\Repository\ThreadRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommonController extends AbstractController
{
    public function addBlocker(): Response
    {
        return new Response('<html lang="fr"><body>add</body></html>');
    }

    public function index(ThreadRepository $threadRepository, ForumRepository $forumRepository, ParameterBagInterface $parameterBag): Response
    {
        $num = $parameterBag->get('numReponse');

        return $this->render('common/index.html.twig', [
            'threads' => $threadRepository->findBy(
                ['forum' => $forumRepository->findOneBy(['slug' => 'nouveautes'])],
                ['dateAjout' => 'DESC'],
                $num, 0
            ),
        ]);
    }

    public function jouer(ParameterBagInterface $parameterBag): Response
    {
        $response = $this->render('common/jouer.html.twig', [
            'gameversion' => $parameterBag->get('unity.version'),
        ]);
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function manuel(): Response
    {
        return $this->render('common/manuel.html.twig');
    }

    public function reglement(): Response
    {
        return $this->render('common/reglement.html.twig');
    }

    public function chat(): Response
    {
        return $this->render('common/chat.html.twig');
    }

    public function faqGenerale(): Response
    {
        return $this->render('common/faqGenerale.html.twig');
    }

    public function faqTechnique(): Response
    {
        return $this->render('common/faqTechnique.html.twig');
    }

    public function team(): Response
    {
        return $this->render('common/team.html.twig');
    }

    public function mentionsLegales(): Response
    {
        return $this->render('common/mentionsLegales.html.twig');
    }

    public function contact(Request $request, TranslatorInterface $translator, MailerInterface $mailer, CsrfTokenManagerInterface $csrfTokenManager, ParameterBagInterface $parameterBag): Response
    {
        if ('POST' === $request->getMethod()) {
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact'.$request->cookies->get('PHPSESSID'), $request->request->get('_token')))) {
                throw new \RuntimeException('CSRF attack detected.');
            }
            $texte = trim($request->get('content'));
            $sujet = trim($request->get('sujet'));
            $email = trim($request->get('email'));
            if (!empty($texte)) {
                $emailContact = $parameterBag->get('mail_admin');

                try {
                    $messageMail = (new TemplatedEmail())
                        ->from(new Address($parameterBag->get('mail_contact'), $parameterBag->get('mail_name')))
                        ->to($emailContact)
                        ->subject('[NT] Contact : '.$sujet)
                        ->htmlTemplate('common/contactEmail.html.twig')
                        ->context([
                            'texte' => $texte,
                            'mail' => $email,
                            'locale' => 'fr',
                        ])
                    ;
                    $mailer->send($messageMail);
                } catch (TransportExceptionInterface $e) {
                }

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $translator->trans('notice.contact')
                );
            }
        }

        return $this->render('common/contact.html.twig');
    }

    public function search(Request $request, ThreadRepository $threadRepository, CommentRepository $commentRepository, ClanRepository $clanRepository, UserRepository $userRepository, ParameterBagInterface $parameterBag): Response
    {
        $num = $parameterBag->get('numReponse');
        $q = (string) $request->get('q');

        // recherche dans les threads
        $threads = $threadRepository->searchThreads(null, null, $q, $num, 1);

        // recherche dans les commentaires
        $comments = $commentRepository->searchComments(null, null, $q, $num, 1);
        foreach ($comments as $comment) {
            $thread = $comment->getThread();
            $finded = false;
            foreach ($threads as $t) {
                if ($thread == $t) {
                    $finded = true;
                    break;
                }
            }
            if (!$finded) {
                $threads[] = $thread;
            }
        }

        return $this->render('common/search.html.twig', [
            'clans' => $clanRepository->searchClans($q, $num, 1),
            'users' => $userRepository->searchUser($q, $num),
            'threads' => $threads,
            'forum' => null,
        ]);
    }
}
