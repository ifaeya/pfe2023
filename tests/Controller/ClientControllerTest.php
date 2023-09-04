<?php

namespace App\Test\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ClientRepository $repository;
    private string $path = '/client/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Client::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'client[nom]' => 'Testing',
            'client[prenom]' => 'Testing',
            'client[numcin]' => 'Testing',
            'client[datecin]' => 'Testing',
            'client[numpasseport]' => 'Testing',
            'client[datepasseport]' => 'Testing',
            'client[datevalidpasport]' => 'Testing',
        ]);

        self::assertResponseRedirects('/client/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setNumcin('My Title');
        $fixture->setDatecin('My Title');
        $fixture->setNumpasseport('My Title');
        $fixture->setDatepasseport('My Title');
        $fixture->setDatevalidpasport('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setNumcin('My Title');
        $fixture->setDatecin('My Title');
        $fixture->setNumpasseport('My Title');
        $fixture->setDatepasseport('My Title');
        $fixture->setDatevalidpasport('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'client[nom]' => 'Something New',
            'client[prenom]' => 'Something New',
            'client[numcin]' => 'Something New',
            'client[datecin]' => 'Something New',
            'client[numpasseport]' => 'Something New',
            'client[datepasseport]' => 'Something New',
            'client[datevalidpasport]' => 'Something New',
        ]);

        self::assertResponseRedirects('/client/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getPrenom());
        self::assertSame('Something New', $fixture[0]->getNumcin());
        self::assertSame('Something New', $fixture[0]->getDatecin());
        self::assertSame('Something New', $fixture[0]->getNumpasseport());
        self::assertSame('Something New', $fixture[0]->getDatepasseport());
        self::assertSame('Something New', $fixture[0]->getDatevalidpasport());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Client();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setNumcin('My Title');
        $fixture->setDatecin('My Title');
        $fixture->setNumpasseport('My Title');
        $fixture->setDatepasseport('My Title');
        $fixture->setDatevalidpasport('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/client/');
    }
}
