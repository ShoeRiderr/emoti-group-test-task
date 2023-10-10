<?php

namespace App\Controller;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\ApiToken;
use App\Entity\User;
use App\Exceptions\UserWithEmailAlreadyExists;
use App\Requests\RegisterRequest;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Throwable;

class ApiSecurityController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    #[Route(path: '/login', name: 'api_login', methods: 'POST')]
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

    #[Route(path: '/logout', name: 'app_logout', methods: 'POST')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/register', name: 'app_register', methods: 'POST')]
    public function register(
        Request $request,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $email = $request->request->get('email');
            $name = $request->request->get('name');
            $password = $request->request->get('password');
            $passwordConfirmation = $request->request->get('passwordConfirmation');

            $validator->validate(
                new RegisterRequest(
                    $email,
                    $name,
                    $password,
                    $passwordConfirmation,
                )
            );

            $user = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($user) {
                throw new UserWithEmailAlreadyExists();
            }
            $user = new User();
            $password = $this->hasher->hashPassword($user, 'pass_1234');

            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $apiToken = new ApiToken();
            $apiToken->setToken($this->generateToken($user));

            $user->addApiToken($apiToken);
            $entityManager->persist($apiToken);
            $entityManager->flush();

            return new JsonResponse($user->toArray());
        } catch (ValidationException $validationException) {
            return new JsonResponse(
                $validationException->getConstraintViolationList(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (UserWithEmailAlreadyExists $error) {
            return new JsonResponse(
                'User with given email already exists.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable $error) {
            $logger->error($error);

            return new JsonResponse('Somethong went wrong. Try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function generateToken(User $user): string
    {
        return $this->hasher->hashPassword($user, rand(1000000, 123544587));
    }
}
