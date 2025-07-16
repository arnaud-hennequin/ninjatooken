<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;

class ResetPasswordControllerTest extends BaseWebTestCase
{
    public function testRequest(): void
    {
        self::markTestSkipped();

        // WITH

        // WHEN
        $this->client->request('GET', '/fr/request');

        // THEN
        self::assertResponseIsSuccessful();
    }

    public function testRequestCheckEmail(): void
    {
        self::markTestSkipped();

        // WITH

        // WHEN
        $this->client->request('GET', '/fr/check-email');

        // THEN
        self::assertResponseIsSuccessful();
    }

    public function testRequestReset(): void
    {
        self::markTestSkipped();

        // WITH

        // WHEN
        $this->client->request('GET', '/fr/reset/');

        // THEN
        self::assertResponseIsSuccessful();
    }
}
