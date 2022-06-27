<?php

namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function get_class;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserByOldPseudo($pseudo = '', $id = 0)
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

    public function searchUser($q = '', $num = 10, $allData = true)
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

    public function getMultiAccount($ip = '', $username = '')
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

    public function getMultiAccountByUser($user = null)
    {
        if (isset($user)) {
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
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Find user by his email.
     */
    public function findUserByEmail($email): array
    {
        return $this->findBy(['emailCanonical' => User::canonicalize($email)]);
    }

    /**
     * Find user by his username.
     */
    public function findUserByUsername($username): array
    {
        return $this->findBy(['usernameCanonical' => User::canonicalize($username)]);
    }

    /**
     * Find user by his email or username.
     */
    public function findUserByUsernameOrEmail($usernameOrEmail): array
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }
}
