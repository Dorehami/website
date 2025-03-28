<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class BannedUserChecker
{
    public function checkPreAuth(mixed $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException(
                'Your account has been banned. Please contact the administrator for more information.'
            );
        }
    }
}
