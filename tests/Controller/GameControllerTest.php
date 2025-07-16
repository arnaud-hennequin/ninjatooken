<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class GameControllerTest extends BaseWebTestCase
{
    public function testOngoingGames(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/fr/parties-en-cours');

        // THEN
        self::assertResponseIsSuccessful();
    }

    public function testJutsuCalculation(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/fr/calculateur-jutsus');

        // THEN
        self::assertResponseIsSuccessful();
    }

    public function testRanking(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/fr/classement/1');

        // THEN
        self::assertResponseIsSuccessful();
    }
}
