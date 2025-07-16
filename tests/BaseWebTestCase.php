<?php

namespace App\Tests;

use App\Tests\DataSet\ClanDataSetup;
use App\Tests\DataSet\UserDataSetup;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected Connection $connection;
    protected UserDataSetup $userDataSetup;
    protected ClanDataSetup $clanDataSetup;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->connection = self::getContainer()->get('doctrine.dbal.default_connection');
        $this->connection->beginTransaction();

        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $faker = \Faker\Factory::create();
        $this->userDataSetup = new UserDataSetup($entityManager);
        $this->clanDataSetup = new ClanDataSetup($entityManager, $faker);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->connection->rollback();

        parent::tearDown();
    }
}
