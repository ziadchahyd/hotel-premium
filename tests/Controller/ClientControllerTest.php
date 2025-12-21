<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ClientControllerTest extends WebTestCase
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

    public function testDashboard(): void
    {
        $client = $this->createClientFixture();
        $this->client->loginUser($client);

        $this->client->request('GET', '/client/dashboard');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tableau de bord Client');
        self::assertSelectorTextContains('body', $client->getEmail());
    }

    public function testAccessRestriction(): void
    {
        $this->client->request('GET', '/client/dashboard');

        self::assertResponseRedirects('/login');
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
}

