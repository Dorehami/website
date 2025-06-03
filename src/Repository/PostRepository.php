<?php

namespace App\Repository;

use App\Entity\Post;
use DateTimeImmutable;
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
    public function findRecentPaginated(int $limit = 10, int $page = 1): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.featuredStartDate IS NULL AND p.featuredEndDate IS NULL')
            ->orderBy('p.createdAt', 'DESC');

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

    /**
     * @return array Returns an array with posts and pagination info sorted by most popular (most comments and votes)
     */
    public function findMostPopularLastDay(int $limit = 5): array
    {
        $queryBuilder = $this->createQueryBuilder('post')
            ->leftJoin('post.votes', 'vote')
            ->leftJoin('post.comments', 'comment')
            ->groupBy('post.id')
            ->orderBy('COUNT(comment.id)', 'DESC')
            ->addOrderBy('COUNT(vote.id)', 'DESC')
            ->where('post.createdAt > :date')
            // this will have to change later to -1 day.
            // we just don't have enough posts right now
            ->setParameter('date', new DateTimeImmutable('-30 days'))
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return array Returns an array with posts and pagination info sorted by most popular (most votes)
     */
    public function findMostPopular(int $limit = 10, int $page = 1): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.featuredEndDate IS NULL AND p.featuredStartDate IS NULL')
            ->leftJoin('p.votes', 'v')
            ->groupBy('p.id')
            ->orderBy('COUNT(v.id)', 'DESC')//->addOrderBy('p.createdAt', 'DESC')   -> probably not relevant for most popular
        ;

        return $this->paginate($queryBuilder, $page, $limit);
    }

    /**
     * Find a post by its URL within a specific time window (e.g., 365 days)
     */
    public function findRecentByNormalizedUrl(string $normalizedUrl, int $daysWindow = 365): ?Post
    {
        $dateThreshold = new DateTimeImmutable("-{$daysWindow} days");

        return $this->createQueryBuilder('p')
            ->where('p.normalizedUrl = :normalizedUrl')
            ->andWhere('p.createdAt > :threshold')
            ->setParameter('normalizedUrl', $normalizedUrl)
            ->setParameter('threshold', $dateThreshold)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find recent posts within a specific time window (e.g., 365 days)
     * @return Post[]
     */
    public function findRecentByThreshold(int $daysWindow = 365): array
    {
        $dateThreshold = new DateTimeImmutable("-{$daysWindow} days");

        return $this->createQueryBuilder('p')
            ->where('p.createdAt > :threshold')
            ->setParameter('threshold', $dateThreshold)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findFeatured(): array
    {
        $today = new DateTimeImmutable("now");
        
        $qb = $this->createQueryBuilder('p')
            ->where('p.featuredStartDate <= :threshold AND p.featuredEndDate >= :threshold')
            ->setParameter('threshold', $today)
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter('threshold', $today);
        
        $results = $qb->getQuery()->getResult();
        
        return $results;
    }
}
