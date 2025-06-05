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
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommonController extends AbstractController
{
    #[Route('/{_locale}/adimages/', name: 'add_blocker')]
    public function addBlocker(): Response
    {
        return new Response('<html lang="fr"><body>add</body></html>');
    }

    #[Route('/{_locale}/', name: 'ninja_tooken_homepage')]
    public function index(ThreadRepository $threadRepository, ForumRepository $forumRepository, ParameterBagInterface $parameterBag): Response
    {
        $num = $parameterBag->get('numReponse');

        return $this->render('common/index.html.twig', [
            'threads' => $threadRepository->findBy(
                ['forum' => $forumRepository->findOneBy(['nom' => 'nouveautes'])],
                ['dateAjout' => 'DESC'],
                $num,
                0
            ),
        ]);
    }

    #[Route('/{_locale}/jouer', name: 'ninja_tooken_jouer')]
    public function jouer(ParameterBagInterface $parameterBag): Response
    {
        $response = $this->render('common/jouer.html.twig', [
            'gameversion' => $parameterBag->get('unity.version'),
        ]);
        $response->setSharedMaxAge(600);

        return $response;
    }

    #[Route('/{_locale}/guide-du-ninja', name: 'ninja_tooken_manuel')]
    public function manuel(): Response
    {
        return $this->render('common/manuel.html.twig');
    }

    #[Route('/{_locale}/regles-respecter', name: 'ninja_tooken_reglement')]
    public function reglement(): Response
    {
        return $this->render('common/reglement.html.twig');
    }

    #[Route('/{_locale}/chat', name: 'ninja_tooken_chat')]
    public function chat(): Response
    {
        return $this->render('common/chat.html.twig');
    }

    #[Route('/{_locale}/faq-generale', name: 'ninja_tooken_faq_generale')]
    public function faqGenerale(): Response
    {
        return $this->render('common/faqGenerale.html.twig');
    }

    #[Route('/{_locale}/faq-technique', name: 'ninja_tooken_faq_technique')]
    public function faqTechnique(): Response
    {
        return $this->render('common/faqTechnique.html.twig');
    }

    #[Route('/{_locale}/team', name: 'ninja_tooken_team')]
    public function team(): Response
    {
        return $this->render('common/team.html.twig');
    }

    #[Route('/{_locale}/mentions-legales', name: 'ninja_tooken_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('common/mentionsLegales.html.twig');
    }

    #[Route('/{_locale}/nous-contacter', name: 'ninja_tooken_contact')]
    public function contact(Request $request, TranslatorInterface $translator, MailerInterface $mailer, CsrfTokenManagerInterface $csrfTokenManager, ParameterBagInterface $parameterBag): Response
    {
        if ('POST' === $request->getMethod()) {
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact'.$request->cookies->getString('PHPSESSID'), $request->request->getString('_token')))) {
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

                $session = $request->getSession();
                if ($session instanceof FlashBagAwareSessionInterface) {
                    $session->getFlashBag()->add(
                        'notice',
                        $translator->trans('notice.contact')
                    );
                }
            }
        }

        return $this->render('common/contact.html.twig');
    }

    #[Route('/{_locale}/search', name: 'ninja_tooken_search')]
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
