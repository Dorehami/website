<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[] Returns an array of Post objects sorted by newest
     */
    public function findNewest(int $limit = 10, int $page = 1): array
    {
        return $this->createBaseQueryBuilder($limit, $page)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Post[] Returns an array of Post objects sorted by most popular (most votes)
     */
    public function findMostPopular(int $limit = 10, int $page = 1): array
    {
        return $this->createBaseQueryBuilder($limit, $page)
            ->orderBy('COUNT(v.id)', 'DESC')
            ->leftJoin('p.votes', 'v')
            ->groupBy('p.id')
            ->addOrderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    private function createBaseQueryBuilder(int $limit = 10, int $page = 1): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->setMaxResults($limit)
            ->setFirstResult($limit * ($page - 1));
    }
}