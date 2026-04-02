<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Domain\Model\AuthToken;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $user;
    private AuthToken $authToken;
    private Photo $photo1;
    private Photo $photo2;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        // Clean up database
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('TRUNCATE TABLE users, photos, auth_tokens, likes RESTART IDENTITY CASCADE');

        $username1 = uniqid('u1_');
        $this->user = new User();
        $this->user
            ->setUsername($username1)
            ->setEmail($username1.'@a.com')
            ->setName('Jan')
            ->setLastName('Kowalski');

        $username2 = uniqid('u2_');
        $user2 = new User();
        $user2
            ->setUsername($username2)
            ->setEmail($username2.'@a.com')
            ->setName('Anna')
            ->setLastName('Nowak');

        $this->photo1 = new Photo();
        $this->photo1
            ->setImageUrl('http://test.com/img1.jpg')
            ->setLocation('Tatry')
            ->setDescription('Góry wysokie')
            ->setCamera('Canon')
            ->setTakenAt(new \DateTimeImmutable('2024-03-15'))
            ->setUser($this->user);

        $this->photo2 = new Photo();
        $this->photo2
            ->setImageUrl('http://test.com/img2.jpg')
            ->setLocation('Bałtyk')
            ->setDescription('Morze spokojne')
            ->setCamera('Sony')
            ->setTakenAt(new \DateTimeImmutable('2024-01-22'))
            ->setUser($user2);

        $this->authToken = new AuthToken();
        $this->authToken
            ->setToken('test-token-'.uniqid())
            ->setUser($this->user);

        $this->entityManager->persist($this->user);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($this->photo1);
        $this->entityManager->persist($this->photo2);
        $this->entityManager->persist($this->authToken);
        $this->entityManager->flush();
    }

    #[Test]
    public function itRendersHomepageForAnonymousUser(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.photo-card');
        $this->assertCount(2, $this->client->getCrawler()->filter('.photo-card'));
    }

    #[Test]
    public function itFiltersByLocation(): void
    {
        $this->client->request('GET', '/', ['location' => 'Tatry']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->client->getCrawler()->filter('.photo-card'));
        $this->assertSelectorTextContains('.photo-meta', 'Tatry');
        $this->assertSelectorTextNotContains('.photo-meta', 'Bałtyk');
    }

    #[Test]
    public function itFiltersByCamera(): void
    {
        $this->client->request('GET', '/', ['camera' => 'Sony']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->client->getCrawler()->filter('.photo-card'));
        $this->assertSelectorTextContains('.photo-meta', 'Sony');
        $this->assertSelectorTextNotContains('.photo-meta', 'Canon');
    }

    #[Test]
    public function itFiltersByDescription(): void
    {
        $this->client->request('GET', '/', ['description' => 'Góry']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->client->getCrawler()->filter('.photo-card'));
        $this->assertSelectorTextContains('.photo-description', 'Góry wysokie');
        $this->assertSelectorTextNotContains('.photo-description', 'Morze spokojne');
    }

    #[Test]
    public function itFiltersByDate(): void
    {
        $this->client->request('GET', '/', ['date' => '2024-01-22']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->client->getCrawler()->filter('.photo-card'));
        $this->assertSelectorTextContains('.author-name', 'Anna Nowak');
        $this->assertSelectorTextNotContains('.author-name', 'Jan Kowalski');
    }

    #[Test]
    public function itFiltersByUsername(): void
    {
        $this->client->request('GET', '/', ['username' => $this->user->getUsername()]);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->client->getCrawler()->filter('.photo-card'));
        $this->assertSelectorTextContains('.author-name', 'Jan Kowalski');
        $this->assertSelectorTextNotContains('.author-name', 'Anna Nowak');
    }

    #[Test]
    public function itFiltersCaseInsensitive(): void
    {
        $this->client->request('GET', '/', ['location' => 'tatry']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $this->client->getCrawler()->filter('.photo-card'));
        $this->assertSelectorTextContains('.photo-meta', 'Tatry');
    }

    #[Test]
    public function itRendersHomepageWithLikesForLoggedInUser(): void
    {
        $this->client->request(
            'POST',
            '/auth/login',
            [
                'username' => $this->user->getUsername(),
                'token' => $this->authToken->getToken(),
            ]
        );
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->client->request('GET', '/photo/'.$this->photo1->getId().'/like');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.photo-card .like-button.liked');
        $this->assertSelectorTextContains('.like-button.liked span', '1');
        $this->assertSelectorTextContains(
            '.photo-card .photo-description',
            $this->photo1->getDescription()
        );
    }
}
