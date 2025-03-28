<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BannedUserVoter extends Voter
{
    const USER_ACTION = 'user_action';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::USER_ACTION;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return true; // Not authenticated, let the security system handle it
        }

        // If user is banned, deny access
        if ($user->isBanned()) {
            return false;
        }

        return true;
    }
}
