<?php

namespace App\Test\Controller;

use App\Entity\Operation;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OperationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private OperationRepository $repository;
    private string $path = '/operation/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Operation::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Operation index');

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
            'operation[createdAt]' => 'Testing',
            'operation[client]' => 'Testing',
            'operation[typeoperation]' => 'Testing',
            'operation[documents]' => 'Testing',
        ]);

        self::assertResponseRedirects('/operation/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Operation();
        $fixture->setCreatedAt('My Title');
        $fixture->setClient('My Title');
        $fixture->setTypeoperation('My Title');
        $fixture->setDocuments('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Operation');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Operation();
        $fixture->setCreatedAt('My Title');
        $fixture->setClient('My Title');
        $fixture->setTypeoperation('My Title');
        $fixture->setDocuments('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'operation[createdAt]' => 'Something New',
            'operation[client]' => 'Something New',
            'operation[typeoperation]' => 'Something New',
            'operation[documents]' => 'Something New',
        ]);

        self::assertResponseRedirects('/operation/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getClient());
        self::assertSame('Something New', $fixture[0]->getTypeoperation());
        self::assertSame('Something New', $fixture[0]->getDocuments());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Operation();
        $fixture->setCreatedAt('My Title');
        $fixture->setClient('My Title');
        $fixture->setTypeoperation('My Title');
        $fixture->setDocuments('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/operation/');
    }
}
