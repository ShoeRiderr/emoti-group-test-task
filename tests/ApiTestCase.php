<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as ApiTestCaseBase;
use App\Entity\User;

class ApiTestCase extends ApiTestCaseBase
{
    protected \Doctrine\ORM\EntityManager $entityManager;
    protected User $user;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()
            ->get('doctrine')
            ->getManager();

            $user = new User();
            $user->setName('test');
            $user->setEmail('test@example.com');
            $user->setPassword('password');

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $this->entityManager->persist($user);

            // actually executes the queries (i.e. the INSERT query)
            $this->entityManager->flush();

            $this->user = $user;
    }
}
