<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user';
    public const API_TOKEN = '12f3600378cbb476613d4cec52b56730bc0bcf77d5925538eeffd8dde2d3c4d71e8bb741f0b8cd6f090c328a7535340bf0e7121786566f1f60501d29';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $password = $this->hasher->hashPassword($user, 'pass_1234');

        $user->setName('user');
        $user->setEmail('user@example.com');
        $user->setPassword($password);
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);
        $manager->flush();

        $apiToken = new ApiToken();
        $apiToken->setToken(self::API_TOKEN);

        $user->addApiToken($apiToken);
        $manager->persist($apiToken);
        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }
}
