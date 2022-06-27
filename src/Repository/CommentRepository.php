<?php

namespace App\Repository;

use App\Entity\Forum\Comment;
use App\Entity\Forum\Forum;
use App\Entity\Forum\Thread;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentsByThread(Thread $thread, $nombreParPage = 5, $page = 1)
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('c')
            ->select(['c', 'a', 'n', 'clu', 'cl'])
            ->where('c.thread = :thread')
            ->leftJoin('c.author', 'a')
            ->leftJoin('a.ninja', 'n')
            ->leftJoin('a.clan', 'clu')
            ->leftJoin('clu.clan', 'cl')
            ->setParameter('thread', $thread)
            ->addOrderBy('c.dateAjout', 'DESC')
            ->getQuery();

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getResult();
    }

    public function getRecentComments(?Forum $forum = null, ?UserInterface $user = null, $num = 0)
    {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.dateAjout', 'DESC');

        if (!empty($forum)) {
            $query->leftJoin('App\Entity\Forum\Thread', 't', 'WITH', 'c.thread = t.id')
                ->andWhere('t.forum = :forum')
                ->setParameter('forum', $forum);
        }
        if (!empty($user)) {
            $query->andWhere('c.author = :user')
                ->setParameter('user', $user);
        }
        $query->setFirstResult(0)
            ->setMaxResults($num);

        return $query->getQuery()->getResult();
    }

    public function getCommentsByAuthor(User $user, $nombreParPage = 10, $page = 1)
    {
        $page = max(1, $page);

        $query = $this->createQueryBuilder('c')
            ->where('c.author = :user')
            ->setParameter('user', $user)
            ->addOrderBy('c.dateAjout', 'DESC');

        $query->setFirstResult(($page - 1) * $nombreParPage)
            ->setMaxResults($nombreParPage);

        return $query->getQuery()->getResult();
    }

    public function searchComments(?User $user = null, ?Forum $forum = null, $q = '', $nombreParPage = 5, $page = 1)
    {
        $query = $this->createQueryBuilder('c')
            ->addOrderBy('c.dateAjout', 'DESC');

        if (!empty($q)) {
            $query->andWhere('c.body LIKE :q')
            ->setParameter('q', '%'.$q.'%');
        }

        if (isset($user)) {
            $query->andWhere('c.author = :user')
            ->setParameter('user', $user);
        }

        if (isset($forum)) {
            $query->innerJoin('App\Entity\Forum\Thread', 't', 'WITH', 'c.thread = t.id')
                ->andWhere('t.forum = :forum')
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
    public function deleteCommentsByThread(?Thread $thread = null): bool
    {
        if ($thread) {
            $query = $this->createQueryBuilder('c')
                ->delete('App\Entity\Forum\Comment', 'c')
                ->where('c.thread = :thread')
                ->setParameter('thread', $thread)
                ->getQuery();

            return 1 === $query->getSingleScalarResult();
        }

        return false;
    }
}
