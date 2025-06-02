<?php

namespace App\Listener;

use App\Entity\User\Ip;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private ObjectManager $em;

    /**
     * Constructor.
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, Doctrine $doctrine)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $doctrine->getManager();
    }

    /**
     * Do the magic.
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var User $user */
            $user = $event->getAuthenticationToken()->getUser();
            $request = $event->getRequest();

            $currentIp = ip2long($request->getClientIp());

            $ip = $this->em->getRepository(Ip::class)
                ->findOneBy(['ip' => $currentIp, 'user' => $user]);

            if (!$ip) {
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
