<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiSecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'api_login')]
    public function login(IriConverterInterface $iriConverter, #[CurrentUser] User $user = null): Response
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

        // Return location for fetching the user in header
        return new Response(null, Response::HTTP_NO_CONTENT, [
            'Location' => $iriConverter->getIriFromResource($user)
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
