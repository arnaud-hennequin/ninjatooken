<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class ClanControllerTest extends BaseWebTestCase
{
    /**
     * @return array<array<int, string>>
     */
    public static function languageProvider(): array
    {
        return [
            ['fr'],
            ['en'],
        ];
    }

    #[DataProvider('languageProvider')]
    public function testHomeClan(string $langue): void
    {
        // WITH
        $this->clanDataSetup->addClan($this->userDataSetup->addUser());

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/clan', $langue));

        // THEN
        self::assertResponseIsSuccessful();
        $this->assertSelectorExists('.content');
    }

    #[DataProvider('languageProvider')]
    public function testClan(string $langue): void
    {
        // WITH
        $clan = $this->clanDataSetup->addClan($this->userDataSetup->addUser());

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/clan/%s', $langue, $clan->getSlug()));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testAddClan(string $langue): void
    {
        // WITH
        $clan = $this->clanDataSetup->addClan($this->userDataSetup->addUser());

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/clan-ajouter', $langue));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testSwitchUser(): void
    {
        $this->markTestIncomplete();
    }

    public function testUpdateClan(): void
    {
        $this->markTestIncomplete();
    }

    public function testDeleteClan(): void
    {
        $this->markTestIncomplete();
    }

    public function testUserDeleteClan(): void
    {
        $this->markTestIncomplete();
    }

    public function testShishouDeleteClan(): void
    {
        $this->markTestIncomplete();
    }

    #[DataProvider('languageProvider')]
    public function testRecrutment(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/compte/clan', $langue));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testDeleteRecrutment(): void
    {
        $this->markTestIncomplete();
    }

    public function testAddRecrutment(): void
    {
        $this->markTestIncomplete();
    }

    public function testAcceptRecrutment(): void
    {
        $this->markTestIncomplete();
    }

    public function testDenyRecrutment(): void
    {
        $this->markTestIncomplete();
    }

    public function testApply(): void
    {
        $this->markTestIncomplete();
    }

    public function testCancelApply(): void
    {
        $this->markTestIncomplete();
    }
}
