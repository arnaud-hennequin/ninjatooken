<?php

namespace App\Tests\DataSet;

use App\Entity\Game\Ninja;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

final class UserDataSetup
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function addUser(): User
    {
        $faker = Factory::create();

        $user = new User();
        $user->setUsername('test_'.$faker->userName());
        $user->setEmail($faker->email());
        $user->setPassword($faker->password());
        $ninja = new Ninja();
        $this->em->persist($ninja);
        $ninja->setUser($user);
        $user->setNinja($ninja);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
