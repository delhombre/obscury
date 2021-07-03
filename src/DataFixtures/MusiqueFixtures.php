<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Musique;
use App\Entity\Category;
use App\Entity\PostLike;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MusiqueFixtures extends Fixture
{
    /**
     * Encodeur de mot de passe
     *
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');
        // $users = [];

        // for ($i=0; $i < 20 ; $i++) { 
        //     $user = new User();
        //     $user->setEmail($faker->email)
        //         ->setUsername($faker->name)
        //         ->setPassword($this->encoder->encodePassword($user, 'password'));
        //     $users[]= $user;
        //     $manager->persist($user);
        // }

        // for ($k=0; $k < 20; $k++) { 
        //     $category = new Category;
        //     $category->setTitle($faker->sentence());

        //     $manager->persist($category);

        //     for ($i=0; $i < 20; $i++) { 
        //         $musique = new Musique;
        //         $musique->setTitle($faker->sentence())
        //                 ->setImage($faker->imageUrl())
        //                 ->setMusic("\musiques\musics\Booba_-_GLAIVE_(Audio)(128k).m4a")
        //                 ->setCreatedAt($faker->dateTimeBetween('-6 months'))
        //                 ->setCategory($category);
                
        //         $manager->persist($musique);

        //         for ($j=0; $j < 10; $j++) { 
        //         $like = new PostLike;
        //         $like->setMusique($musique)
        //              ->setUser($faker->randomElement($users));   
                     
        //         $manager->persist($like);


        //         }

        //     }
        // }

        // $manager->flush();
    }
}
