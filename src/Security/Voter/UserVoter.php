<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    public const MANAGE = 'USER_MANAGE';
    public const USE = 'USER_USER';
    public const ANONYMOUS = 'USER_ANONYMOUS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::MANAGE, self::USE, self::ANONYMOUS]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {


            case self::MANAGE:
                if (in_array('ROLE_ADMIN',$user->getRoles())) {
                    return true;
                }
                break;
            case self::USE:
                if (in_array('ROLE_SUPERUSER',$user->getRoles())){
                    return true;
                }
                break;
            case self::ANONYMOUS:
                if (in_array('ROLE_USERTEMPORARY',$user->getRoles())){
                    return true;
                }
                break;

        }

        return false;
    }
}
