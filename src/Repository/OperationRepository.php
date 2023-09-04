<?php

namespace App\Repository;

use App\Entity\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Operation>
 *
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }
    public function save(Operation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Operation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findAllSortedByDateDesc(): array
{
    return $this->createQueryBuilder('o')
        ->orderBy('o.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}


//    /**
//     * @return Operation[] Returns an array of Operation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Operation
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
/*public function countOperationsByType(string $type): int
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o)')
        ->andWhere('o.typeoperation = :type')
        ->setParameter('type', $type)
        ->getQuery()
        ->getSingleScalarResult();
}*/

public function countOperationsByType(string $type): int
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o)')
        ->join('o.typeoperation', 't')
        ->andWhere('t.Libelle = :type') // Utilisez 't.Libelle' pour le champ libelle du type d'opération
        ->setParameter('type', $type)
        ->getQuery()
        ->getSingleScalarResult();
}
public function countAchatOperationsToday(): int
{
    $today = new \DateTime('today'); // Obtient la date actuelle à minuit
    $tomorrow = new \DateTime('tomorrow'); // Obtient la date de demain à minuit

    return $this->createQueryBuilder('o')
        ->select('COUNT(o)')
        ->join('o.typeoperation', 't')
        ->andWhere('t.Libelle = :achat')
        ->andWhere('o.createdAt >= :today AND o.createdAt < :tomorrow')
        ->setParameter('achat', 'achat')
        ->setParameter('today', $today)
        ->setParameter('tomorrow', $tomorrow)
        ->getQuery()
        ->getSingleScalarResult();
}

public function countVenteOperationsToday(): int
{
    $today = new \DateTime('today'); // Obtient la date actuelle à minuit
    $tomorrow = new \DateTime('tomorrow'); // Obtient la date de demain à minuit

    return $this->createQueryBuilder('o')
        ->select('COUNT(o)')
        ->join('o.typeoperation', 't')
        ->andWhere('t.Libelle = :vente')
        ->andWhere('o.createdAt >= :today AND o.createdAt < :tomorrow')
        ->setParameter('vente', 'vente')
        ->setParameter('today', $today)
        ->setParameter('tomorrow', $tomorrow)
        ->getQuery()
        ->getSingleScalarResult();
}

public function findOperationsAchat(): array
{
    // Logique pour récupérer les opérations d'achat
    // Par exemple, vous pouvez utiliser une requête DQL pour filtrer les opérations d'achat
    return $this->createQueryBuilder('o')
        ->andWhere('o.typeoperation = :achat')
        ->setParameter('achat', 'achat') // Assurez-vous que la valeur correspond au libellé de l'opération d'achat
        ->getQuery()
        ->getResult();
}

public function findOperationsVente(): array
{
    // Logique pour récupérer les opérations de vente
    // Par exemple, vous pouvez utiliser une requête DQL pour filtrer les opérations de vente
    return $this->createQueryBuilder('o')
        ->andWhere('o.typeoperation = :vente')
        ->setParameter('vente', 'vente') // Assurez-vous que la valeur correspond au libellé de l'opération de vente
        ->getQuery()
        ->getResult();
}
  /**
     * Retourne les opérations du type spécifié (achat ou vente).
     * @param string $type Le type d'opération (achat ou vente)
     * @return Operation[]
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('o')
        ->join('o.typeoperation', 't')
        ->andWhere('t.Libelle = :type') // Utilisez 't.Libelle' pour le champ libelle du type d'opération
        ->setParameter('type', $type)
        ->getQuery()
            ->getResult();
    }

}
