<?php

namespace App\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\User\MessageUser;
 
class MessageUserListener
{
    protected $container;
    protected $mailer;

    public function __construct(ContainerInterface $container, \Swift_Mailer $mailer)
    {
        $this->container = $container;
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
            if ($destinataire->getReceiveAvertissement() && $destinataire->getConfirmationToken() === null) {
                $message = $entity->getMessage();
                $user = $message->getAuthor();

                $swiftMessage = (new \Swift_Message('[NT] nouveau message de la part de '.$user->getUsername()))
                    ->setFrom([$this->container->getParameter('mail_contact') => $this->container->getParameter('mail_name')])
                    ->setTo($destinataire->getEmail())
                    ->setContentType("text/html")
                    ->setBody($this->container->get('twig')->render('user/avertissementEmail.html.twig', [
                        'user' => $user,
                        'message' => $message,
                        'locale' => $destinataire->getLocale()
                    ]));

                $this->mailer->send($swiftMessage);
            }
        }
    }
}