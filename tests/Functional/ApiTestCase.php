<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as ApiTestCaseBase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

abstract class ApiTestCase extends ApiTestCaseBase
{
    protected ?EntityManager $entityManager;
    protected User $user;
    protected ApiToken $apiToken;
    protected AbstractDatabaseTool $databaseTool;
    protected Client $client;

    protected const API_TOKEN = '12f3600378cbb476613d4cec52b56730bc0bcf77d5925538eeffd8dde2d3c4d71e8bb741f0b8cd6f090c328a7535340bf0e7121786566f1f60501d29';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->entityManager = self::getContainer()
            ->get('doctrine')
            ->getManager();

            $user = new User();
            $user->setName('test');
            $user->setEmail('test@example.com');
            $user->setPassword('password');

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $apiToken = new ApiToken();
            $apiToken->setToken(self::API_TOKEN);
            $user->addApiToken($apiToken);
            $this->entityManager->persist($apiToken);
            // actually executes the queries (i.e. the INSERT query)
            $this->entityManager->flush();

            $this->user = $user;
            $this->apiToken = $apiToken;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
