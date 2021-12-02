<?php

namespace App\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use App\Entity\User\MessageUser;
 
class MessageUserListener
{
    protected $params;
    protected $twig;
    protected $mailer;

    public function __construct(ParameterBagInterface $params, Environment $twig, MailerInterface $mailer)
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

                try {
                    $messageMail = (new TemplatedEmail())
                        ->from(new Address($this->params->get('mail_contact'), $this->params->get('mail_name')))
                        ->to($destinataire->getEmail())
                        ->subject('[NT] nouveau message de la part de '.$user->getUsername())
                        ->htmlTemplate('user/avertissementEmail.html.twig')
                        ->context([
                            'user' => $user,
                            'message' => $message,
                            'locale' => $destinataire->getLocale()
                        ])
                    ;
                    $this->mailer->send($messageMail);
                } catch (TransportExceptionInterface $e) {}

                $destinataire->setDateMessage(new \DateTime);
                $em->persist($destinataire);
                $em->flush();
            }
        }
    }
}