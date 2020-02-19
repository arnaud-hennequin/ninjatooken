<?php

namespace NinjaTooken\UserBundle\Security\Authorization\Voter;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ClientIpVoter implements VoterInterface
{
    protected $requestStack;
    protected $blacklistedIp;

    public function __construct(RequestStack $requestStack, array $blacklistedIp = array())
    {
        $this->requestStack    = $requestStack;
        $this->blacklistedIp = $blacklistedIp;
    }

    public function supportsAttribute($attribute)
    {
        // you won't check against a user attribute, so return true
        return true;
    }

    public function supportsClass($class)
    {
        // your voter supports all type of token classes, so return true
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getClientIp(), $this->blacklistedIp)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
