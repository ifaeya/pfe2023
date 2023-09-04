<?php

namespace App\Test\Controller;

use App\Entity\Caisse;
use App\Repository\CaisseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CaisseControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CaisseRepository $repository;
    private string $path = '/caisse/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Caisse::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Caisse index');

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
            'caisse[libelle]' => 'Testing',
            'caisse[solde]' => 'Testing',
            'caisse[fermee]' => 'Testing',
            'caisse[devises]' => 'Testing',
        ]);

        self::assertResponseRedirects('/caisse/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Caisse();
        $fixture->setLibelle('My Title');
        $fixture->setSolde('My Title');
        $fixture->setFermee('My Title');
        $fixture->setDevises('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Caisse');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Caisse();
        $fixture->setLibelle('My Title');
        $fixture->setSolde('My Title');
        $fixture->setFermee('My Title');
        $fixture->setDevises('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'caisse[libelle]' => 'Something New',
            'caisse[solde]' => 'Something New',
            'caisse[fermee]' => 'Something New',
            'caisse[devises]' => 'Something New',
        ]);

        self::assertResponseRedirects('/caisse/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getLibelle());
        self::assertSame('Something New', $fixture[0]->getSolde());
        self::assertSame('Something New', $fixture[0]->getFermee());
        self::assertSame('Something New', $fixture[0]->getDevises());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Caisse();
        $fixture->setLibelle('My Title');
        $fixture->setSolde('My Title');
        $fixture->setFermee('My Title');
        $fixture->setDevises('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/caisse/');
    }
}
