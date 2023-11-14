<?php

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\PostCategory;
use App\Entity\PostComment;
use App\Entity\Post;
use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private static $users = [
        [
            'role' => ['ROLE_SUPER_ADMIN'],
            'username' => 'sa',
            'password' => 'sa'
        ],
        [
            'role' => ['ROLE_ADMIN'],
            'username' => 'a',
            'password' => 'a'
        ],
        [
            'role' => [],
            'username' => 'u',
            'password' => 'u'
        ]
    ];

    private static $postCategories = [
        [
            'name' => 'Lorem',
            'color' => '#d1786e'
        ],
        [
            'name' => 'Ipsum',
            'color' => '#d16ebd'
        ],
        [
            'name' => 'Dolor',
            'color' => '#9477bc'
        ],
    ];

    private static $fileTypes = [
        'txt', 'pdf', 'jpg', 'mp3', 'webm'
    ];

    private $userPasswordHasher;
    private $slugger;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $filesystem = new Filesystem();


        // USERS
        foreach (self::$users as $value) {
            $user = new Account();
            $user->setRoles($value['role'])
                ->setDiscordId($faker->randomNumber(9, true))
                ->setUsername($value['username'])
                ->setPassword(
                    $this->userPasswordHasher->hashPassword($user, $value['password'])
                );

            $manager->persist($user);
        }

        $manager->flush();


        // CATEGORIES
        $filesystem->copy('src/DataFixtures/assets/categories/placeholder-TEST.webp', 'public/uploads/categories/placeholder-TEST.webp');
        foreach(self::$postCategories as $value) {
            $category = new PostCategory();
            $category->setName($value['name'])
                ->setColor($value['color'])
                ->setImage('placeholder-TEST.webp');

            $manager->persist($category);
        }

        $manager->flush();


        // POSTS
        $filesystem->copy('src/DataFixtures/assets/posts/placeholder-TEST.webp', 'public/uploads/posts/placeholder-TEST.webp');
        $categories = $manager->getRepository(PostCategory::class)->findAll();
        for ($i = 666; $i > 0; $i--) {
            $post = new Post();
            $title = $faker->sentence(4);
            $post->setTitle($title)
                ->setSlug($this->slugger->slug($title)->lower())
                ->setContent($faker->paragraphs(4, true))
                ->setCategory($faker->randomElement($categories))
                ->setDate(new \DateTimeImmutable('-'.$i.' days'));
            if (rand(0, 1)) {
                $post->setImage('placeholder-TEST.webp');
            }

            $manager->persist($post);
        }

        $manager->flush();


        // COMMENTS
        $posts = $manager->getRepository(Post::class)->findAll();
        $accounts = $manager->getRepository(Account::class)->findAll();
        foreach ($posts as $post) {
            if (rand(0, 1)) {
                for ($i = 0; $i < rand(3, 9); $i++) {
                    $comment = new PostComment();
                    $comment->setPost($post)
                        ->setContent($faker->paragraph(4))
                        ->setAccount($faker->randomElement($accounts))
                        ->setDate($post->getDate());

                    $manager->persist($comment);
                }
            }
        }


        // FILES
        foreach(self::$fileTypes as $value) {
            $filesystem->copy('src/DataFixtures/assets/files/placeholder-TEST.'.$value, 'public/uploads/files/placeholder-TEST.'.$value);
        }
        for ($i = 666; $i > 0; $i--) {
            $file = new File();
            $file->setName('placeholder')
                ->setHash('TEST')
                ->setType($faker->randomElement(self::$fileTypes))
                ->setSize(rand(100, 100000000000))
                ->setDate(new \DateTimeImmutable('-'.$i.' days'))
                ->setPrivate(rand(0, 1))
                ->setAccount($faker->randomElement($accounts));

            $manager->persist($file);
        }


        $manager->flush();
    }
}
