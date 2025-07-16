<?php

namespace App\Tests\DataSet;

use App\Entity\Clan\Clan;
use App\Entity\Clan\ClanUtilisateur;
use App\Entity\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;

final class ClanDataSetup
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Generator $faker,
    ) {
    }

    public function addClan(?UserInterface $author = null): Clan
    {
        $clan = new Clan();
        $clan->setNom('test_'.$this->faker->company());
        $clan->setDescription($this->faker->sentence());

        $clanMember = new ClanUtilisateur();
        $clanMember->setMembre($author);

        $this->em->persist($clanMember);
        $clan->addMembre($clanMember);

        $this->em->persist($clan);
        $this->em->flush();

        return $clan;
    }
}
