<?php

namespace App\Tests\Controller;

use App\Tests\Utils\DataSeedTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommonControllerTest extends WebTestCase
{
    use DataSeedTrait;

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
    public function testHome(string $langue): void
    {
        // WITH
        $client = static::createClient();

        // WHEN
        $crawler = $client->request('GET', sprintf('/%s/', $langue));

        // THEN
        self::assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.flagSocial'));
        $this->assertCount(1, $crawler->filter('.home'));
        $this->assertCount(1, $crawler->filter('.presentation'));
        $this->assertCount(1, $crawler->filter('.create'));
        $this->assertCount(1, $crawler->filter('.diaporama'));
        $this->assertCount(1, $crawler->filter('.account'));
    }

    #[DataProvider('languageProvider')]
    public function testLoggedHome(string $langue): void
    {
        // WITH
        $client = static::createClient();
        $loggedUser = $this->createUser(static::getContainer()->get(EntityManagerInterface::class));

        // WHEN
        $client->loginUser($loggedUser);
        $crawler = $client->request('GET', sprintf('/%s/', $langue));

        // THEN
        self::assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('.account'));// accountLogged
    }
}
