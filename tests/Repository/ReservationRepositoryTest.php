<?php

namespace App\Tests\Repository;

use App\Entity\Chambre;
use App\Entity\ClassementH;
use App\Entity\Client;
use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ReservationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $manager;
    private ReservationRepository $repository;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->manager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Reservation::class);
        $this->passwordHasher = $kernel->getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testIsChambreAvailable(): void
    {
        $chambre = $this->createChambreFixture();
        $client = $this->createClientFixture();

        $checkIn = new \DateTimeImmutable('+10 days');
        $checkOut = new \DateTimeImmutable('+15 days');

        $available = $this->repository->isChambreAvailable($chambre, $checkIn, $checkOut);
        self::assertTrue($available);

        $this->createReservationFixture($client, $chambre, $checkIn, $checkOut);

        $available = $this->repository->isChambreAvailable($chambre, $checkIn, $checkOut);
        self::assertFalse($available);
    }

    public function testFindByClient(): void
    {
        $client = $this->createClientFixture();
        $chambre = $this->createChambreFixture();

        $this->createReservationFixture($client, $chambre);
        $this->createReservationFixture($client, $chambre);

        $reservations = $this->repository->findByClient($client);
        self::assertCount(2, $reservations);
    }

    public function testGetCurrentlyOccupiedChambres(): void
    {
        $client = $this->createClientFixture();
        $chambre = $this->createChambreFixture();

        $checkIn = new \DateTimeImmutable('-2 days');
        $checkOut = new \DateTimeImmutable('+2 days');

        $this->createReservationFixture($client, $chambre, $checkIn, $checkOut);

        $occupied = $this->repository->getCurrentlyOccupiedChambres();
        self::assertCount(1, $occupied);
    }

    private function createClientFixture(): Client
    {
        $client = new Client();
        $client->setEmail('client@test.com');
        $client->setPassword($this->passwordHasher->hashPassword($client, 'password123'));
        $client->setRoles(['ROLE_CLIENT']);
        $client->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($client);
        $this->manager->flush();

        return $client;
    }

    private function createChambreFixture(): Chambre
    {
        $hotelRepo = $this->manager->getRepository(Hotel::class);
        $classementRepo = $this->manager->getRepository(ClassementH::class);

        $hotel = $hotelRepo->findOneBy([]);
        $classement = $classementRepo->findOneBy([]);

        if (!$hotel) {
            $hotel = new Hotel();
            $hotel->setName('Test Hotel');
            $hotel->setAdress('123 Test');
            $hotel->setCity('Test City');
            $hotel->setCreatedAt(new \DateTimeImmutable());
            $this->manager->persist($hotel);
        }

        if (!$classement) {
            $classement = new ClassementH();
            $classement->setName('Standard');
            $classement->setBasePrice(100.0);
            $this->manager->persist($classement);
        }

        $chambre = new Chambre();
        $chambre->setNumber(301);
        $chambre->setFloor(3);
        $chambre->setArea(35.0);
        $chambre->setPricePerNight(200.0);
        $chambre->setIsAvailable(true);
        $chambre->setHotel($hotel);
        $chambre->setClassement($classement);

        $this->manager->persist($chambre);
        $this->manager->flush();

        return $chambre;
    }

    private function createReservationFixture(
        Client $client,
        Chambre $chambre,
        ?\DateTimeImmutable $checkIn = null,
        ?\DateTimeImmutable $checkOut = null
    ): Reservation {
        $reservation = new Reservation();
        $reservation->setClient($client);
        $reservation->setChambre($chambre);
        $reservation->setCheckIn($checkIn ?? new \DateTimeImmutable('+10 days'));
        $reservation->setCheckOut($checkOut ?? new \DateTimeImmutable('+15 days'));
        $reservation->setStatus('pending');
        $reservation->setTotalPrice(500.0);

        $this->manager->persist($reservation);
        $this->manager->flush();

        return $reservation;
    }
}



