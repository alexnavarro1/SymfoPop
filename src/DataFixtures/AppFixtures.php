<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    // Recupero aquest encriptador previ de la injecció del codi de symfony quan faig cridar-ho
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // Executo aquesta classe d'emplenat total massiva al llançar un "load".
    public function load(ObjectManager $manager): void
    {
        // Carrego el creador i falsificador de Faker només de la llengua nativa catalana.
        $faker = Factory::create('ca_ES');
        // Un petit emmagatzemat per distribuir aleatoriament més aviat usuaris als productes de després.
        $users = [];

        // Genero manualment els primers grans 5 usuaris d'entrada! Així ja n'hauré bastants a l'atzar.
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setName($faker->name());
            
            // La contrasenya genèrica pel meu accés com a tester encriptada a la forca d'un simple hash segur ('password')
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password'
            );
            $user->setPassword($hashedPassword);
            
            // Deixo en safata un abans de llençar per salvar dades asíncronament!
            $manager->persist($user);
            $users[] = $user;
        }

        // Toca els segons: Em faig de cop 20 nous articles preparats per a penjar de tots colors.
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setTitle($faker->sentence(3));
            $product->setDescription($faker->paragraphs(3, true));
            $product->setPrice($faker->randomFloat(2, 5, 500));
            // Utilitzo internet on picsum em traurà del nores de random imatges ràpides gràcies al Faker! (Així no falla de cache visual)
            $product->setImage('https://picsum.photos/seed/' . $faker->uuid() . '/600/400');
            // Els desvio de dates passades aleatòriament a l'esquena al cap dels ultims 30 dies d'activitat normal (un mes a present)
            $product->setCreatedAt($faker->dateTimeBetween('-1 month', 'now'));
            
            // Trie al vol o a la sort una sort d'un dels 5 previs falsificats abans des de la meva taula
            $randomUser = $faker->randomElement($users);
            $product->setOwner($randomUser);

            $manager->persist($product);
        }

        // Ara sí que envio les accions completament en "bloc massiu" al MySQL gràcies al manegador
        $manager->flush();
    }
}
