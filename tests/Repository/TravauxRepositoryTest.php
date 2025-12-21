<?php

namespace App\Tests\Repository;

use App\Entity\Chambre;
use App\Entity\ClassementH;
use App\Entity\Hotel;
use App\Entity\Travaux;
use App\Repository\TravauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TravauxRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $manager;
    private TravauxRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->manager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Travaux::class);
    }

    public function testFindLate(): void
    {
        $chambre = $this->createChambreFixture();

        $travauxEnRetard = new Travaux();
        $travauxEnRetard->setTitle('Travaux en retard');
        $travauxEnRetard->setDescription('Description');
        $travauxEnRetard->setStartDate(new \DateTimeImmutable('-10 days'));
        $travauxEnRetard->setEndDate(new \DateTimeImmutable('-2 days'));
        $travauxEnRetard->setIsDone(false);
        $travauxEnRetard->setChambre($chambre);

        $travauxTermine = new Travaux();
        $travauxTermine->setTitle('Travaux terminé');
        $travauxTermine->setDescription('Description');
        $travauxTermine->setStartDate(new \DateTimeImmutable('-10 days'));
        $travauxTermine->setEndDate(new \DateTimeImmutable('-2 days'));
        $travauxTermine->setIsDone(true);
        $travauxTermine->setChambre($chambre);

        $this->manager->persist($travauxEnRetard);
        $this->manager->persist($travauxTermine);
        $this->manager->flush();

        $lateTravaux = $this->repository->findLate();

        self::assertCount(1, $lateTravaux);
        self::assertSame('Travaux en retard', $lateTravaux[0]->getTitle());
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
        $chambre->setNumber(401);
        $chambre->setFloor(4);
        $chambre->setArea(40.0);
        $chambre->setPricePerNight(250.0);
        $chambre->setIsAvailable(true);
        $chambre->setHotel($hotel);
        $chambre->setClassement($classement);

        $this->manager->persist($chambre);
        $this->manager->flush();

        return $chambre;
    }
}

