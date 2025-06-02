<?php

namespace App\Repository;

use App\Entity\Forum\Forum;
use App\Entity\Forum\Thread;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Thread>
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    /**
     * @return Paginator<Thread>
     */
    public function getThreads(Forum $forum, int $nombreParPage = 5, int $page = 1): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('t')
            ->where('t.forum = :forum')
            ->setParameter('forum', $forum)
            ->addOrderBy('t.isPostit', 'DESC')
            ->addOrderBy('t.lastCommentAt', 'DESC')
            ->getQuery();

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return new Paginator($query);
    }

    /**
     * @return Paginator<Thread>
     */
    public function getEvents(int $nombreParPage = 5, int $page = 1): Paginator
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('t')
            ->where('t.isEvent = :isEvent')
            ->setParameter('isEvent', true)
            ->addOrderBy('t.isPostit', 'DESC')
            ->addOrderBy('t.lastCommentAt', 'DESC')
            ->getQuery();

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return new Paginator($query);
    }

    /**
     * @return array<int, Thread>
     */
    public function searchThreads(
        ?User $user = null,
        ?Forum $forum = null,
        string $q = '',
        int $nombreParPage = 5,
        int $page = 1,
    ): array {
        $query = $this->createQueryBuilder('t')
            ->addOrderBy('t.isPostit', 'DESC')
            ->addOrderBy('t.lastCommentAt', 'DESC');

        if (!empty($q)) {
            $query->andWhere('t.nom LIKE :q')
                ->andWhere('t.body LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }

        if (isset($user)) {
            $query->andWhere('t.author = :user')
                ->setParameter('user', $user);
        }

        if (isset($forum)) {
            $query->andWhere('t.forum = :forum')
                ->setParameter('forum', $forum);
        }

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function deleteThreadsByForum(?Forum $forum = null): bool
    {
        if ($forum) {
            $query = $this->createQueryBuilder('t')
                ->delete('App\Entity\Forum\Thread', 't')
                ->where('t.forum = :forum')
                ->setParameter('forum', $forum)
                ->getQuery();

            return 1 === $query->getSingleScalarResult();
        }

        return false;
    }
}
