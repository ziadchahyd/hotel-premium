<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Vérifier si un client avec cet email existe déjà
        $existingClient = $manager->getRepository(Client::class)->findOneBy(['email' => 'client@example.com']);
        
        if (!$existingClient) {
            $client = new Client();
            $client->setEmail('client@example.com');
            $client->setFirstName('Jean');
            $client->setLastName('Dupont');
            $client->setRoles(['ROLE_CLIENT']);
            $client->setPassword($this->passwordHasher->hashPassword($client, 'password123'));
            
            $manager->persist($client);
        }

        $manager->flush();
    }
}