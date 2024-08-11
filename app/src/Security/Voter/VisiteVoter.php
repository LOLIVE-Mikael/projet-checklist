<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Visite;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
class VisiteVoter extends Voter
{
    // Define your constants for actions
    const VIEW = 'view_visite';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Only support specific action and subject type
        return in_array($attribute, [self::VIEW])
            && $subject instanceof \App\Entity\Visite;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // You know $subject is a Visite object, thanks to supports() method
        /** @var Visite $visite */
        $visite = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($visite, $user);
        }

        return false;
    }

    private function canView(Visite $visite, User $user): bool
    {
        // If user has admin or manager role, grant access
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MANAGER')) {
            return true;
        }

        // If user is the technician assigned to the visit, grant access
        if ($visite->getUser() === $user) {
            return true;
        }

        // Deny access otherwise
        return false;
    }
}
