<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminControllerTest extends WebTestCase
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

    public function testLogin(): void
    {
        $admin = $this->createAdminFixture();
        
        $crawler = $this->client->request('GET', '/admin/login');

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('body', 'Connexion');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $admin->getEmail(),
            '_password' => 'password123',
        ]);

        $this->client->submit($form);

        $this->client->followRedirect();
        self::assertResponseStatusCodeSame(200);
    }

    public function testDashboard(): void
    {
        $admin = $this->createAdminFixture();
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Dashboard Administrateur');
    }

    public function testOccupation(): void
    {
        $admin = $this->createAdminFixture();
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/occupation');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Occupation');
    }

    public function testOccupationRange(): void
    {
        $admin = $this->createAdminFixture();
        $this->client->loginUser($admin);

        $this->client->request('GET', '/admin/occupation/range');

        self::assertResponseStatusCodeSame(200);
    }

    private function createAdminFixture(): Admin
    {
        $admin = new Admin();
        $admin->setEmail('admin@test.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $admin->setRoles(['ROLE_ADMIN']);

        $this->manager->persist($admin);
        $this->manager->flush();

        return $admin;
    }
}



