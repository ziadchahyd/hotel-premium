<?php

namespace App\Tests\Controller;

use App\Entity\Chambre;
use App\Entity\ClassementH;
use App\Entity\Client;
use App\Entity\Hotel;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ReservationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testIndex(): void
    {
        $client = $this->createClientFixture();
        $this->client->loginUser($client);

        $this->client->request('GET', '/reservation');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Reservation');
    }

    public function testNew(): void
    {
        $client = $this->createClientFixture();
        $chambre = $this->createChambreFixture();
        $this->client->loginUser($client);

        $crawler = $this->client->request('GET', '/reservation/new');

        self::assertResponseStatusCodeSame(200);

        $checkIn = new \DateTimeImmutable('+7 days');
        $checkOut = new \DateTimeImmutable('+10 days');

        $form = $crawler->selectButton('Réserver')->form([
            'reservation[hotel]' => $chambre->getHotel()->getId(),
            'reservation[checkIn][year]' => $checkIn->format('Y'),
            'reservation[checkIn][month]' => $checkIn->format('n'),
            'reservation[checkIn][day]' => $checkIn->format('j'),
            'reservation[checkOut][year]' => $checkOut->format('Y'),
            'reservation[checkOut][month]' => $checkOut->format('n'),
            'reservation[checkOut][day]' => $checkOut->format('j'),
            'reservation[chambre]' => $chambre->getId(),
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/reservation');
    }

    public function testShow(): void
    {
        $client = $this->createClientFixture();
        $chambre = $this->createChambreFixture();
        $reservation = $this->createReservationFixture($client, $chambre);
        $this->client->loginUser($client);

        $this->client->request('GET', sprintf('/reservation/%s', $reservation->getId()));

        self::assertResponseStatusCodeSame(200);
    }

    public function testCancel(): void
    {
        $client = $this->createClientFixture();
        $chambre = $this->createChambreFixture();
        $reservation = $this->createReservationFixture($client, $chambre);
        $this->client->loginUser($client);

        $this->client->request('POST', sprintf('/reservation/%s/cancel', $reservation->getId()), [
            '_token' => static::getContainer()->get('security.csrf.token_manager')->getToken('cancel'.$reservation->getId()),
        ]);

        self::assertResponseRedirects('/reservation');

        $this->manager->refresh($reservation);
        self::assertSame('cancelled', $reservation->getStatus());
    }

    public function testApiChambresDisponibles(): void
    {
        $client = $this->createClientFixture();
        $chambre = $this->createChambreFixture();
        $this->client->loginUser($client);

        $checkIn = new \DateTimeImmutable('+7 days');
        $checkOut = new \DateTimeImmutable('+10 days');

        $this->client->request('GET', '/reservation/api/chambres-disponibles', [
            'hotel' => $chambre->getHotel()->getId(),
            'checkIn' => $checkIn->format('Y-m-d'),
            'checkOut' => $checkOut->format('Y-m-d'),
        ]);

        self::assertResponseStatusCodeSame(200);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('chambres', $response);
    }

    private function createClientFixture(): Client
    {
        $client = new Client();
        $client->setEmail('client@test.com');
        $client->setPassword($this->passwordHasher->hashPassword($client, 'password123'));
        $client->setRoles(['ROLE_CLIENT']);
        $client->setFirstName('Test');
        $client->setLastName('Client');
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
            $hotel->setAdress('123 Test Street');
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
        $chambre->setNumber(201);
        $chambre->setFloor(2);
        $chambre->setArea(30.0);
        $chambre->setPricePerNight(150.0);
        $chambre->setIsAvailable(true);
        $chambre->setHotel($hotel);
        $chambre->setClassement($classement);

        $this->manager->persist($chambre);
        $this->manager->flush();

        return $chambre;
    }

    private function createReservationFixture(Client $client, Chambre $chambre): Reservation
    {
        $reservation = new Reservation();
        $reservation->setClient($client);
        $reservation->setChambre($chambre);
        $reservation->setCheckIn(new \DateTimeImmutable('+7 days'));
        $reservation->setCheckOut(new \DateTimeImmutable('+10 days'));
        $reservation->setStatus('pending');
        $reservation->setTotalPrice(450.0);

        $this->manager->persist($reservation);
        $this->manager->flush();

        return $reservation;
    }
}



