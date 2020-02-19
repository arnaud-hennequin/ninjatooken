<?php

namespace NinjaTooken\UserBundle\Listener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use NinjaTooken\UserBundle\Entity\User;

class RegistrationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess'
        ];
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $request = $event->getRequest();
        /*
        // active l'utilisateur et envoi un mail de confirmation du mail
        $user->setEnabled(true);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        if (null === $user->getAutoLogin()) {
            $user->setAutoLogin($this->tokenGenerator->generateToken());
        }
        $this->mailer->sendConfirmationEmailMessage($user);

        $this->userManager->updateUser($user);*/
    }
}