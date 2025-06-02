<?php

namespace App\Listener;

use App\Entity\User\MessageUser;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class MessageUserListener
{
    protected ParameterBagInterface $params;
    protected Environment $twig;
    protected MailerInterface $mailer;

    public function __construct(ParameterBagInterface $params, Environment $twig, MailerInterface $mailer)
    {
        $this->params = $params;
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    // message d'avertissement
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof MessageUser && null !== $entity->getDestinataire()) {
            $destinataire = $entity->getDestinataire();

            // envoyer un message d'avertissement par mail
            if ($destinataire->getReceiveAvertissement() && null === $destinataire->getConfirmationToken() && $destinataire->getDateMessage() < new \DateTime('today')) {
                $message = $entity->getMessage();
                $user = $message->getAuthor();

                try {
                    $messageMail = (new TemplatedEmail())
                        ->from(new Address($this->params->get('mail_contact'), $this->params->get('mail_name')))
                        ->to($destinataire->getEmail())
                        ->subject('[NT] nouveau message de la part de '.$user->getUsername())
                        ->htmlTemplate('user/avertissementEmail.html.twig')
                        ->context([
                            'user' => $user,
                            'message' => $message,
                            'locale' => $destinataire->getLocale(),
                        ])
                    ;
                    $this->mailer->send($messageMail);
                } catch (TransportExceptionInterface) {
                }

                $destinataire->setDateMessage(new \DateTime());
                $em->persist($destinataire);
                $em->flush();
            }
        }
    }
}
