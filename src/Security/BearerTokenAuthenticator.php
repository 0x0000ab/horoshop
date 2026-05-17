<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): ?bool
    {
        $authHeader = $request->headers->get('Authorization');

        return $authHeader && str_starts_with($authHeader, 'Bearer ');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authHeader = $request->headers->get('Authorization');
        $token = substr($authHeader, 7);

        if (!$token) {
            throw new AuthenticationException('No Bearer token provided');
        }

        $user = $this->loadUserFromToken($token);

        return new SelfValidatingPassport(
            new UserBadge($token, function (string $token) {
                // Resolve user from token here
                return $this->loadUserFromToken($token);
            })
        );
    }

    private function loadUserFromToken(string $token)
    {
        $user = $this->userRepository->findByAccessToken($token);
        if (!$user) {
            throw new \Exception('Auth fail. Access token not correct');
        }

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return null; // continue request
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Unauthorized', 401);
    }
}
