<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * @return array Returns an array with posts and pagination info sorted by newest
     */
    public function findNewest(int $limit = 10, int $page = 1): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        return $this->paginate($queryBuilder, $page, $limit);
    }

    /**
     * @return array Returns an array with posts and pagination info sorted by most popular (most votes)
     */
    public function findMostPopular(int $limit = 10, int $page = 1): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.votes', 'v')
            ->groupBy('p.id')
            ->orderBy('COUNT(v.id)', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC');

        return $this->paginate($queryBuilder, $page, $limit);
    }

    /**
     * Paginates a query and returns both results and pagination metadata
     */
    private function paginate(QueryBuilder $queryBuilder, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);

        $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder);

        $total = count($paginator);
        $lastPage = ceil($total / $limit);

        return [
            'posts' => $paginator->getIterator(),
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $limit,
                'total' => $total,
                'has_previous' => $page > 1,
                'has_next' => $page < $lastPage,
                'previous_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $lastPage ? $page + 1 : null,
            ]
        ];
    }
}
