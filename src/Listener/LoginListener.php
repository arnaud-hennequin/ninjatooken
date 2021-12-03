<?php

namespace App\Listener;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use App\Entity\User\Ip;

class LoginListener
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private ObjectManager $em;

    /**
     * Constructor
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Doctrine $doctrine
     */
    public function __construct( AuthorizationCheckerInterface $authorizationChecker , Doctrine $doctrine )
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $doctrine->getManager();
    }

    /**
    * Do the magic.
    *
    * @param InteractiveLoginEvent $event
    */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $event->getAuthenticationToken()->getUser();
            $request = $event->getRequest();

            $currentIp = ip2long($request->getClientIp());

            $ip = $this->em->getRepository(Ip::class)
                ->findOneBy(array('ip' => $currentIp, 'user' => $user));

            if(!$ip){
                $ip = new Ip();
                $ip->setIp($currentIp);
                $ip->setUser($user);
            }
            $ip->setUpdatedAt(new \DateTime());

            $this->em->persist($ip);
            $this->em->flush();
        }
    }
}