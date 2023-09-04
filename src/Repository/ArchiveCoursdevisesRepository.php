<?php

namespace App\Repository;

use App\Entity\Devises;
use App\Entity\ArchiveCoursdevises;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ArchiveCoursdevises>
 *
 * @method ArchiveCoursdevises|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveCoursdevises|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveCoursdevises[]    findAll()
 * @method ArchiveCoursdevises[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchiveCoursdevisesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveCoursdevises::class);
    }

    public function save(ArchiveCoursdevises $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArchiveCoursdevises $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ArchiveCoursdevises[] Returns an array of ArchiveCoursdevises objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ArchiveCoursdevises
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
{
    return $this->createQueryBuilder('a')
        ->andWhere('a.dateArchivage BETWEEN :start AND :end')
        ->setParameter('start', $startDate)
        ->setParameter('end', $endDate)
        ->orderBy('a.dateArchivage', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findByDay(\DateTimeInterface $day)
    {
        $startOfDay = \DateTimeImmutable::createFromMutable($day)->setTime(0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromMutable($day)->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->where('a.dateArchivage BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getResult();
    }
    public function getValeurAchatForDate(Devises $devise, \DateTimeInterface $date): ?float
    {
        $lastArchive = $this->findLastArchiveBeforeDate($devise, $date);

        return $lastArchive ? $lastArchive->getValeurachat() : null;
    }

    public function findLastArchiveBeforeDate(Devises $devise, \DateTimeInterface $date): ?ArchiveCoursdevises
    {
        return $this->createQueryBuilder('a')
            ->where('a.cours = :devise')
            ->andWhere('a.dateArchivage < :date')
            ->orderBy('a.dateArchivage', 'DESC')
            ->setMaxResults(1)
            ->setParameter('devise', $devise)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }
  
}






