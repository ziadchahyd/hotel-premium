<?php

namespace App\Tests\Controller;

use App\Entity\Chambre;
use App\Entity\ClassementH;
use App\Entity\Hotel;
use App\Entity\Travaux;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TravauxControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $travauxRepository;
    private EntityRepository $chambreRepository;
    private string $path = '/travaux/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->travauxRepository = $this->manager->getRepository(Travaux::class);
        $this->chambreRepository = $this->manager->getRepository(Chambre::class);

        foreach ($this->travauxRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Travaux index');
    }

    public function testNew(): void
    {
        $chambre = $this->createChambreFixture();
        
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $startDate = new \DateTimeImmutable('2025-01-01');
        $endDate = new \DateTimeImmutable('2025-01-15');

        $this->client->submitForm('Save', [
            'travaux[title]' => 'Test Travaux',
            'travaux[description]' => 'Description test',
            'travaux[startDate][year]' => '2025',
            'travaux[startDate][month]' => '1',
            'travaux[startDate][day]' => '1',
            'travaux[endDate][year]' => '2025',
            'travaux[endDate][month]' => '1',
            'travaux[endDate][day]' => '15',
            'travaux[isDone]' => false,
            'travaux[chambre]' => $chambre->getId(),
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->travauxRepository->count([]));
    }

    public function testShow(): void
    {
        $chambre = $this->createChambreFixture();
        $travaux = $this->createTravauxFixture($chambre);

        $this->client->request('GET', sprintf('%s%s', $this->path, $travaux->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Travaux');
        self::assertSelectorTextContains('body', $travaux->getTitle());
    }

    public function testEdit(): void
    {
        $chambre = $this->createChambreFixture();
        $travaux = $this->createTravauxFixture($chambre);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $travaux->getId()));

        $this->client->submitForm('Update', [
            'travaux[title]' => 'Titre Modifié',
            'travaux[description]' => 'Description modifiée',
            'travaux[startDate][year]' => '2025',
            'travaux[startDate][month]' => '2',
            'travaux[startDate][day]' => '1',
            'travaux[endDate][year]' => '2025',
            'travaux[endDate][month]' => '2',
            'travaux[endDate][day]' => '15',
            'travaux[isDone]' => true,
            'travaux[chambre]' => $chambre->getId(),
        ]);

        self::assertResponseRedirects('/travaux/');

        $updatedTravaux = $this->travauxRepository->find($travaux->getId());
        self::assertSame('Titre Modifié', $updatedTravaux->getTitle());
        self::assertSame('Description modifiée', $updatedTravaux->getDescription());
        self::assertTrue($updatedTravaux->isDone());
    }

    public function testRemove(): void
    {
        $chambre = $this->createChambreFixture();
        $travaux = $this->createTravauxFixture($chambre);

        $this->client->request('GET', sprintf('%s%s', $this->path, $travaux->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/travaux/');
        self::assertSame(0, $this->travauxRepository->count([]));
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
        $chambre->setNumber(101);
        $chambre->setFloor(1);
        $chambre->setArea(25.0);
        $chambre->setPricePerNight(100.0);
        $chambre->setIsAvailable(true);
        $chambre->setHotel($hotel);
        $chambre->setClassement($classement);

        $this->manager->persist($chambre);
        $this->manager->flush();

        return $chambre;
    }

    private function createTravauxFixture(Chambre $chambre): Travaux
    {
        $travaux = new Travaux();
        $travaux->setTitle('Test Travaux');
        $travaux->setDescription('Description test');
        $travaux->setStartDate(new \DateTimeImmutable('2025-01-01'));
        $travaux->setEndDate(new \DateTimeImmutable('2025-01-15'));
        $travaux->setIsDone(false);
        $travaux->setChambre($chambre);

        $this->manager->persist($travaux);
        $this->manager->flush();

        return $travaux;
    }
}
