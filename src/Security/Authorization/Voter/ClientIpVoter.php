<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ClientIpVoter implements VoterInterface
{
    /**
     * @param array<int, string> $blacklistedIp
     */
    public function __construct(
        protected RequestStack $requestStack,
        protected array $blacklistedIp = [],
    ) {
    }

    public function supportsAttribute(): bool
    {
        // you won't check against a user attribute, so return true
        return true;
    }

    public function supportsClass(): bool
    {
        // your voter supports all type of token classes, so return true
        return true;
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getClientIp(), $this->blacklistedIp)) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
