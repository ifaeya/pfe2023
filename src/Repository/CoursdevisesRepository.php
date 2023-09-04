<?php

namespace App\Repository;

use App\Entity\Coursdevises;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Coursdevises>
 *
 * @method Coursdevises|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coursdevises|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coursdevises[]    findAll()
 * @method Coursdevises[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursdevisesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coursdevises::class);
        parent::__construct($registry, Coursdevises::class);

    }

    public function save(Coursdevises $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Coursdevises $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Coursdevises[] Returns an array of Coursdevises objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Coursdevises
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findByDevisesAndSens($devisesFrom, $devisesTo, $sens)
{
    return $this->createQueryBuilder('cours')
        ->where('cours.devisesFrom = :devisesFrom')
        ->andWhere('cours.devisesTo = :devisesTo')
        ->andWhere('cours.sens = :sens')
        ->setParameter('devisesFrom', $devisesFrom)
        ->setParameter('devisesTo', $devisesTo)
        ->setParameter('sens', $sens)
        ->getQuery()
        ->getOneOrNullResult();
}


public function findAllSortedByDateDesc(): array
{
    return $this->createQueryBuilder('o')
        ->orderBy('o.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}
public function findByUpdatedAt()
{
    return $this->createQueryBuilder('c')
        ->orderBy('c.updatedAt', 'DESC')
        ->getQuery()
        ->getResult();
}


public function findByDay()
{
    return $this->createQueryBuilder('c')
        ->select('c')
        ->groupBy('DATE(c.updatedAt)')
        ->getQuery()
        ->getResult();
}
public function calculateValueDifference(Coursdevises $cours, \DateTimeInterface $startDate, \DateTimeInterface $endDate): ?float
{
    $startArchive = $this->createQueryBuilder('a')
        ->select('a.valeurvente')
        ->andWhere('a.cours = :cours')
        ->andWhere('a.dateArchivage <= :start')
        ->orderBy('a.dateArchivage', 'DESC')
        ->setParameter('cours', $cours)
        ->setParameter('start', $startDate)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    $endArchive = $this->createQueryBuilder('a')
        ->select('a.valeurvente')
        ->andWhere('a.cours = :cours')
        ->andWhere('a.dateArchivage <= :end')
        ->orderBy('a.dateArchivage', 'DESC')
        ->setParameter('cours', $cours)
        ->setParameter('end', $endDate)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    if ($startArchive && $endArchive) {
        return $endArchive['valeurvente'] - $startArchive['valeurvente'];
    }

    return null;
}
public function findAllByCours(Coursdevises $cours): array
{
    return $this->createQueryBuilder('a')
        ->andWhere('a.cours = :cours')
        ->setParameter('cours', $cours)
        ->orderBy('a.dateArchivage', 'DESC')
        ->getQuery()
        ->getResult();
}

}
