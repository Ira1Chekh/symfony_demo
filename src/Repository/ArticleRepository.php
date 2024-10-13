<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Tag;
use App\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 *
 * @template-extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findLatest(int $page = 1, ?Tag $tag = null): Paginator
    {
        $qb = $this->createQueryBuilder('articles')
            ->addSelect('author', 'tags')
            ->innerJoin('articles.author', 'author')
            ->leftJoin('articles.tags', 'tags')
            ->where('articles.publishedAt <= :now')
            ->orderBy('articles.publishedAt', 'DESC')
            ->setParameter('now', new \DateTimeImmutable())
        ;

        if (null !== $tag) {
            $qb->andWhere(':tag MEMBER OF articles.tags')
                ->setParameter('tag', $tag);
        }

        return (new Paginator($qb))->paginate($page);
    }

    public function findLatestNew(int $page = 1, ?Tag $tag = null): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('u', 't')  // Fetch related entities
            ->innerJoin('a.author', 'u')
            ->leftJoin('a.tags', 't')
            //->where('articles.publishedAt <= :now')
            //->setParameter('now', new \DateTimeImmutable())
            //->orderBy('a.publishedAt', 'DESC')
        ;

        // Add tag filter if a tag is provided
        if (null !== $tag) {
            $qb->andWhere(':tag MEMBER OF a.tags')
               ->setParameter('tag', $tag);
        }


        return (new Paginator($qb))->paginate(1);
    }
}
