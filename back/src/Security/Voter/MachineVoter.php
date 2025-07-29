<?php

namespace App\Security\Voter;

use App\Entity\Machine;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Summary of MachineVoter.
 */
final class MachineVoter extends Voter
{
    public const EDIT = 'MACHINE_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof Machine;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (in_array('ROLE_EDITOR', $user->getRoles(), true)) {
            return true;
        }

        return false;
    }
}
