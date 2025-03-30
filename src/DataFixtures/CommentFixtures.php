<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public const NUM_COMMENTS_PER_POST = 5;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fa_IR');

        for ($i = 1; $i <= UserFixtures::USER_COUNT; $i++) {
            $author = $this->getReference('user-' . UserFixtures::USER_COUNT - $i + 1, User::class);

            for ($j = 1; $j <= PostFixtures::NUM_POSTS_PER_USER; $j++) {
                $post = $this->getReference('post-' . $i . '-' . $j, Post::class);

                for ($t = 1; $t <= self::NUM_COMMENTS_PER_POST; $t++) {
                    $comment = new Comment();

                    $comment->setAuthor($author);
                    $comment->setPost($post);
                    $comment->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 years', 'now')));
                    $comment->setVisible(true);
                    $comment->setContent($faker->realText(random_int(10, 100)));

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }
}
