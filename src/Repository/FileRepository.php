<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<File>
 *
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function add(File $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(File $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function queryForPagination(bool $granted): Query
    {
        $query = $this->createQueryBuilder('f')
            ->leftJoin('f.account', 'a')
            ->orderBy('f.id', 'DESC');

        if (!$granted) {
            $query->andWhere('f.private = :val')
                ->setParameter('val', $granted);
        }

        return $query->getQuery();
    }

    public function search(string $value, int $max, bool $granted): array
    {
        $query = $this->createQueryBuilder('f')
            ->andWhere('LOWER(f.name) LIKE LOWER(:val)')
            ->setParameter('val', '%'.$value.'%');

        if (!$granted) {
            $query->andWhere('f.private = :granted')
                ->setParameter('granted', $granted);
        }

        return $query->orderBy('f.id', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPreviousFile(File $value, bool $granted): ?File
    {
        $query = $this->createQueryBuilder('f')
            ->andWhere('f.id < :val')
            ->setParameter('val', $value->getId());

        if (!$granted) {
            $query->andWhere('f.private = :granted')
                ->setParameter('granted', $granted);
        }

        return $query->orderBy('f.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findNextFile(File $value, bool $granted): ?File
    {
        $query = $this->createQueryBuilder('f')
            ->andWhere('f.id > :val')
            ->setParameter('val', $value->getId());

        if (!$granted) {
            $query->andWhere('f.private = :granted')
                ->setParameter('granted', $granted);
        }

        return $query->orderBy('f.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function totalSizeUsed(): ?int
    {
        return $this->createQueryBuilder('f')
            ->select('SUM(f.size)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

//    /**
//     * @return File[] Returns an array of File objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?File
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
