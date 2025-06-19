<?php

namespace App\Tests\Controller;

use App\Tests\Utils\DataSeedTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClanControllerTest extends WebTestCase
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
    public function testHomeClan(string $langue): void
    {
        // WITH
        $client = static::createClient();
        $this->createClan(static::getContainer()->get(EntityManagerInterface::class));

        // WHEN
        $crawler = $client->request('GET', sprintf('/%s/clan', $langue));

        // THEN
        self::assertResponseIsSuccessful();
        $this->assertSelectorExists('.content');
    }
}
