<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/v1/api/users')]
final class UsersController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/{id}', name: 'app_users_get', methods: ['GET'])]
    #[IsGranted('GET_USER', 'user')]
    public function index(?User $user): Response
    {
        return $this->json([
            'status' => 1,
            'data' => $user->toArray(),
        ]);
    }

    #[Route('', name: 'app_users_create', methods: ['POST'])]
    #[IsGranted('CREATE_USER')]
    public function create(
        Request   $request,
        Validator $entityValidator
    ): Response
    {
        $user = new User();
        $data = $request->toArray();

        $user->setLogin($data['login'] ?? null);
        $user->setPhone($data['phone'] ?? null);
        $user->setPass($data['pass'] ?? null);

        $errors = $entityValidator->validate($user);

        if (count($errors) > 0) {
            return $this->json([
                'status' => 0,
                'errors' => $errors,
            ], 500);
        }

        $this->userRepository->save($user, true);

        return $this->json([
            'status' => 1,
            'data' => $user->toArray(),
        ]);
    }

    #[Route('/{id}', name: 'app_users_edit', methods: ['PUT'])]
    #[IsGranted('EDIT_USER', 'user')]
    public function edit(
        ?User      $user,
        Request   $request,
        Validator $entityValidator
    ): Response
    {
        $data = $request->toArray();
        $user->load($data);

        $errors = $entityValidator->validate($user);

        if (count($errors) > 0) {
            return $this->json([
                'status' => 0,
                'errors' => $errors,
            ], 500);
        }

        $this->userRepository->save($user, true);

        return $this->json([
            'status' => 1,
            'data' => $user->toArray(),
        ]);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['DELETE'])]
    #[IsGranted('DELETE_USER', 'user')]
    public function delete(?User $user): Response
    {
        $this->userRepository->remove($user, true);

        return $this->json([
            'status' => 1
        ]);
    }

}
