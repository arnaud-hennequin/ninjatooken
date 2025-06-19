<?php

namespace App\Tests\Utils;

use App\Entity\Clan\Clan;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Game\Ninja;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

trait DataSeedTrait
{
    public function createUser(EntityManagerInterface $em): User
    {
        $faker = Factory::create();

        $user = new User();
        $user->setUsername('test_'.$faker->userName());
        $user->setEmail($faker->email());
        $user->setPassword($faker->password());
        $ninja = new Ninja();
        $em->persist($ninja);
        $ninja->setUser($user);
        $user->setNinja($ninja);
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function createClan(EntityManagerInterface $em): Clan
    {
        $faker = Factory::create();

        $clan = new Clan();
        $clan->setNom("test_".$faker->company());
        $clan->setDescription($faker->sentence());

        $clanMember = new ClanUtilisateur();
        $clanMember->setMembre($this->createUser($em));

        $em->persist($clanMember);
        $clan->addMembre($clanMember);

        $em->persist($clan);
        $em->flush();

        return $clan;
    }
}