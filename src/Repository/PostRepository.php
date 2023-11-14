<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Post;
use App\Entity\PostCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function add(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function queryForPagination(): Query
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->orderBy('p.id', 'DESC')
            ->getQuery();
    }

    public function findPreviousPost(Post $value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id < :val')
            ->setParameter('val', $value->getId())

            ->andWhere('p.category = :cat')
            ->setParameter('cat', $value->getCategory())

            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findNextPost(Post $value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id > :val')
            ->setParameter('val', $value->getId())

            ->andWhere('p.category = :cat')
            ->setParameter('cat', $value->getCategory())

            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function search(string $value, int $max): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.title) LIKE LOWER(:val)')
            ->setParameter('val', '%'.$value.'%')

            ->orderBy('p.id', 'DESC')
            ->setMaxResults($max)

            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Post[] Returns an array of Post objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
