<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class UnityControllerTest extends BaseWebTestCase
{
    public function testUnityUpdate(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/unity/xml/game/update');

        // THEN
        self::assertResponseIsSuccessful();
    }

    public function testUnityConnect(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/unity/xml/game/connect', server: ['X-COMMON' => 'localhost']);

        // THEN
        self::assertResponseIsSuccessful();
    }
}
