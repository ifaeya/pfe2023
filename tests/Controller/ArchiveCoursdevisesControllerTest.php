<?php

namespace App\Test\Controller;

use App\Entity\ArchiveCoursdevises;
use App\Repository\ArchiveCoursdevisesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArchiveCoursdevisesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ArchiveCoursdevisesRepository $repository;
    private string $path = '/archive/coursdevises/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(ArchiveCoursdevises::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ArchiveCoursdevise index');

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
            'archive_coursdevise[dateArchivage]' => 'Testing',
            'archive_coursdevise[valeurachat]' => 'Testing',
            'archive_coursdevise[valeurvente]' => 'Testing',
            'archive_coursdevise[profit]' => 'Testing',
            'archive_coursdevise[createdAt]' => 'Testing',
            'archive_coursdevise[cours]' => 'Testing',
        ]);

        self::assertResponseRedirects('/archive/coursdevises/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new ArchiveCoursdevises();
        $fixture->setDateArchivage('My Title');
        $fixture->setValeurachat('My Title');
        $fixture->setValeurvente('My Title');
        $fixture->setProfit('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setCours('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ArchiveCoursdevise');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new ArchiveCoursdevises();
        $fixture->setDateArchivage('My Title');
        $fixture->setValeurachat('My Title');
        $fixture->setValeurvente('My Title');
        $fixture->setProfit('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setCours('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'archive_coursdevise[dateArchivage]' => 'Something New',
            'archive_coursdevise[valeurachat]' => 'Something New',
            'archive_coursdevise[valeurvente]' => 'Something New',
            'archive_coursdevise[profit]' => 'Something New',
            'archive_coursdevise[createdAt]' => 'Something New',
            'archive_coursdevise[cours]' => 'Something New',
        ]);

        self::assertResponseRedirects('/archive/coursdevises/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateArchivage());
        self::assertSame('Something New', $fixture[0]->getValeurachat());
        self::assertSame('Something New', $fixture[0]->getValeurvente());
        self::assertSame('Something New', $fixture[0]->getProfit());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getCours());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new ArchiveCoursdevises();
        $fixture->setDateArchivage('My Title');
        $fixture->setValeurachat('My Title');
        $fixture->setValeurvente('My Title');
        $fixture->setProfit('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setCours('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/archive/coursdevises/');
    }
}
