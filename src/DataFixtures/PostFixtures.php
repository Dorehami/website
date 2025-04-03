<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Service\UrlNormalizerService;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public const int NUM_POSTS_PER_USER = 3;

    public function __construct(
        private readonly UrlNormalizerService $urlNormalizer
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fa_IR');

        for ($i = 1; $i <= UserFixtures::USER_COUNT; $i++) {
            $author = $this->getReference('user-' . $i, User::class);

            for ($j = 1; $j <= self::NUM_POSTS_PER_USER; $j++) {
                $post = new Post();

                $randHour = rand(1, 48);
                $createdDateTime = (new DateTime())->sub(new DateInterval("PT{$randHour}H"));

                $post->setAuthor($author);
                $post->setTitle($faker->realText(20));
                $post->setUrl($faker->url);
                $post->setCreatedAt(DateTimeImmutable::createFromMutable($createdDateTime));
                $post->setNormalizedUrl(
                    $this->urlNormalizer->normalize($post->getUrl())
                );

                $this->addReference('post-' . $i . '-' . $j, $post);

                $manager->persist($post);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
