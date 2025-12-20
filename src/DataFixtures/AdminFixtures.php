<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Vérifier si un admin avec cet email existe déjà
        $existingAdmin = $manager->getRepository(Admin::class)->findOneBy(['email' => 'admin@hotelpremium.com']);
        
        if (!$existingAdmin) {
            $admin = new Admin();
            $admin->setEmail('admin@hotelpremium.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            
            $manager->persist($admin);
        }

        $manager->flush();
    }
}