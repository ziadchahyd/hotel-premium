<?php

namespace App\DataFixtures;

use App\Entity\Hotel;
use App\Entity\Chambre;
use App\Entity\ClassementH;
use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer un classement
        $classement = new ClassementH();
        $classement->setName('Standard');
        $classement->setDescription('Chambre standard');
        $classement->setBasePrice(50.00);
        $manager->persist($classement);

        // Créer un service
        $service = new Service();
        $service->setName('WiFi');
        $service->setDescription('Connexion WiFi gratuite');
        $manager->persist($service);

        // Créer un hôtel
        $hotel = new Hotel();
        $hotel->setName('Zalagh');
        $hotel->setAdress('123 Avenue Mohammed V');
        $hotel->setCity('Casablanca');
        $hotel->setDescription('Hôtel de luxe au centre de Casablanca');
        $hotel->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($hotel);

        // Créer des chambres pour cet hôtel
        for ($i = 1; $i <= 10; $i++) {
            $chambre = new Chambre();
            $chambre->setNumber($i);
            $chambre->setFloor(rand(1, 5));
            $chambre->setArea(rand(20, 40));
            $chambre->setPricePerNight(rand(50, 150));
            $chambre->setIsAvailable(true);
            $chambre->setHotel($hotel);
            $chambre->setClassement($classement);
            $chambre->addService($service);
            
            $manager->persist($chambre);
        }

        $manager->flush();
    }
}