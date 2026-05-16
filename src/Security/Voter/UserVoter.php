<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    const ACT_GET = 'GET_USER';
    const ACT_CREATE = 'CREATE_USER';
    const ACT_EDIT = 'EDIT_USER';
    const ACT_DELETE = 'DELETE_USER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACT_GET, self::ACT_CREATE, self::ACT_EDIT, self::ACT_DELETE]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            throw new HttpException(403, 'The user must be logged in to access this resource.');
        }

        $isRootUser = in_array(User::ROLE_ROOT, $user->getRoles());
        $isUser = in_array(User::ROLE_USER, $user->getRoles());

        if (!$isRootUser && !$isUser) {
            throw new HttpException(403, 'Access denied.');
        }

        if ($attribute === self::ACT_CREATE) {
            return true;
        }

        if ($isUser && $attribute === self::ACT_DELETE) {
            throw new HttpException(403, 'Access denied.');
        }

        if (!$subject) {
            throw new HttpException(404, 'User not found');
        }

        $status = false;

        if (in_array($attribute, [self::ACT_GET, self::ACT_EDIT])) {
            $status = $isRootUser || $subject->getId() === $user->getId();
        } else if ($attribute === self::ACT_DELETE) {
            $status = $isRootUser;
        }

        if (!$status) {
            throw new HttpException(403, 'Only: GET, POST, PUT allowed');
        }

        return $status;
    }
}
