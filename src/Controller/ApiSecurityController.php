<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiSecurityController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    #[Route(path: '/login', name: 'api_login')]
    public function login(#[CurrentUser] User $user = null): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json([
                'error' => 'Invalid login lequest: Check that Content-type header is "application/json".'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$user) {
            return $this->json([
                'error' => 'Invalid login credentials.',
            ]);
        }

        return new JsonResponse($user->toArray());
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function register(Request $request)
    {
    }

    protected function generateToken(User $user, string $token): string
    {
        return $this->hasher->hashPassword($user, $token);
    }
}
