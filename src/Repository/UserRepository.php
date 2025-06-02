<?php

namespace App\Repository;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserByOldPseudo(string $pseudo = '', int $id = 0): ?User
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.enabled = :enabled')
            ->setParameter('enabled', true);

        if (!empty($pseudo)) {
            $query->andWhere('u.oldUsernamesCanonical LIKE :pseudo')
                ->setParameter('pseudo', ','.$pseudo.',');
        }

        if (!empty($id)) {
            $query->andWhere('u.id <> :id')
                ->setParameter('id', $id);
        }
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array<int, User>
     */
    public function searchUser(string $q = '', int $num = 10, bool $allData = true): array
    {
        $query = $this->createQueryBuilder('u');

        if (!$allData) {
            $query->select('u.username as text, u.id');
        }

        $query->where('u.locked = :locked')
            ->setParameter('locked', false);

        if (!empty($q)) {
            $query->andWhere('u.username LIKE :q')
                ->setParameter('q', $q.'%');
        }

        $query->orderBy('u.username', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($num);

        return $query->getQuery()->getResult();
    }

    /**
     * @return array<int, User>
     */
    public function getMultiAccount(string $ip = '', string $username = ''): array
    {
        if (empty($ip) && empty($username)) {
            return [];
        }

        if (!empty($username)) {
            $query = $this
                ->createQueryBuilder('u')
                ->select('ip.ip')
                ->join('u.ips', 'ip');

            if (!empty($ip)) {
                $query
                    ->andWhere('ip.ip = :ip')
                    ->setParameter('ip', $ip);
            }

            $query
                ->andWhere('u.username = :username')
                ->setParameter('username', $username);

            $ips = $query->getQuery()->getResult();
            if (count($ips) > 0) {
                $ips = array_values($ips[0]);
            } else {
                $ips = [$ip];
            }
        } else {
            $ips = [$ip];
        }

        $query = $this->createQueryBuilder('u')
            ->join('u.ips', 'ip')
            ->where('ip.ip IN(:ips)')
            ->setParameter('ips', $ips)
            ->orderBy('u.username', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(20);

        return $query->getQuery()->getResult();
    }

    /**
     * @return array<int, User>
     */
    public function getMultiAccountByUser(?User $user = null): array
    {
        if ($user !== null) {
            $query = $this
                ->createQueryBuilder('u')
                ->select('ip.ip')
                ->join('u.ips', 'ip')
                ->andWhere('u = :user')
                ->setParameter('user', $user);

            $ips = $query->getQuery()->getResult();
            if (!empty($ips)) {
                $ips = array_values($ips[0]);

                $query = $this->createQueryBuilder('u')
                    ->join('u.ips', 'ip')
                    ->where('ip.ip IN(:ips)')
                    ->setParameter('ips', $ips)
                    ->orderBy('u.username', 'ASC')
                    ->setFirstResult(0)
                    ->setMaxResults(20);

                return $query->getQuery()->getResult();
            }
        }

        return [];
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @return array<int, User>
     */
    public function findUserByEmail(string $email): array
    {
        return $this->findBy(['emailCanonical' => User::canonicalize($email)]);
    }

    /**
     * @return array<int, User>
     */
    public function findUserByUsername(string $username): array
    {
        return $this->findBy(['usernameCanonical' => User::canonicalize($username)]);
    }

    /**
     * @return array<int, User>
     */
    public function findUserByUsernameOrEmail(string $usernameOrEmail): array
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }
}
