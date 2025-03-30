<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const int USER_COUNT = 10;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::USER_COUNT; $i++) {
            $user = new User();

            $user->setAvatarUrl('https://picsum.photos/200/300');
            $user->setEmail('email' . $i . '@local.host');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setRoles(['ROLE_USER']);

            $this->addReference('user-' . $i, $user);

            $manager->persist($user);
        }

        $admin = new User();
        $admin->setAvatarUrl('https://picsum.photos/200/300');
        $admin->setEmail('admin@local.host');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $manager->flush();
    }
}
