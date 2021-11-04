<?php

namespace App\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use App\Entity\User\MessageUser;
 
class MessageUserListener
{
    protected $params;
    protected $twig;
    protected $mailer;

    public function __construct(ParameterBagInterface $params, Environment $twig, \Swift_Mailer $mailer)
    {
        $this->params = $params;
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    // message d'avertissement
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof MessageUser && $entity->getDestinataire() !== null) {
            $destinataire = $entity->getDestinataire();

            // envoyer un message d'avertissement par mail
            if ($destinataire->getReceiveAvertissement() && $destinataire->getConfirmationToken() === null && $destinataire->getDateMessage() < new \DateTime('today')) {
                $message = $entity->getMessage();
                $user = $message->getAuthor();

                $swiftMessage = (new \Swift_Message('[NT] nouveau message de la part de '.$user->getUsername()))
                    ->setFrom([$this->params->get('mail_contact') => $this->params->get('mail_name')])
                    ->setTo($destinataire->getEmail())
                    ->setContentType("text/html")
                    ->setBody($this->twig->render('user/avertissementEmail.html.twig', [
                        'user' => $user,
                        'message' => $message,
                        'locale' => $destinataire->getLocale()
                    ]));

                $this->mailer->send($swiftMessage);

                $destinataire->setDateMessage(new \DateTime);
                $em->persist($destinataire);
                $em->flush();
            }
        }
    }
}