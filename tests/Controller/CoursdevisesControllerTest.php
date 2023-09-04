<?php

namespace App\Test\Controller;

use App\Entity\Coursdevises;
use App\Repository\CoursdevisesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoursdevisesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CoursdevisesRepository $repository;
    private string $path = '/coursdevises/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Coursdevises::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Coursdevise index');

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
            'coursdevise[valeurachat]' => 'Testing',
            'coursdevise[valeurvente]' => 'Testing',
            'coursdevise[createdAt]' => 'Testing',
            'coursdevise[updatedAt]' => 'Testing',
            'coursdevise[devises]' => 'Testing',
        ]);

        self::assertResponseRedirects('/coursdevises/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Coursdevises();
        $fixture->setValeurachat('My Title');
        $fixture->setValeurvente('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setDevises('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Coursdevise');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Coursdevises();
        $fixture->setValeurachat('My Title');
        $fixture->setValeurvente('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setDevises('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'coursdevise[valeurachat]' => 'Something New',
            'coursdevise[valeurvente]' => 'Something New',
            'coursdevise[createdAt]' => 'Something New',
            'coursdevise[updatedAt]' => 'Something New',
            'coursdevise[devises]' => 'Something New',
        ]);

        self::assertResponseRedirects('/coursdevises/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getValeurachat());
        self::assertSame('Something New', $fixture[0]->getValeurvente());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
        self::assertSame('Something New', $fixture[0]->getDevises());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Coursdevises();
        $fixture->setValeurachat('My Title');
        $fixture->setValeurvente('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setDevises('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/coursdevises/');
    }
}
