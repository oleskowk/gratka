<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Domain\Model\AuthToken;
use App\Domain\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $user;
    private AuthToken $authToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $username = uniqid('u_');
        $this->user = new User();
        $this->user
            ->setUsername($username)
            ->setEmail($username.'@example.com')
            ->setName('Jan')
            ->setLastName('Kowalski')
            ->setAge(20)
            ->setBio('Z');

        $this->authToken = new AuthToken();
        $this->authToken
            ->setToken('test-token-'.uniqid())
            ->setUser($this->user);

        $this->entityManager->persist($this->user);
        $this->entityManager->persist($this->authToken);
        $this->entityManager->flush();
    }

    #[Test]
    public function itRedirectsAnonymousUserToHome(): void
    {
        // WHEN
        $this->client->request('GET', '/profile');

        // THEN
        $this->assertResponseRedirects('/');
    }

    #[Test]
    public function itShowsProfileForLoggedInUser(): void
    {
        // GIVEN
        $this->client->request(
            'GET',
            sprintf('/auth/%s/%s', $this->user->getUsername(), $this->authToken->getToken())
        );
        $this->assertResponseRedirects('/');

        // WHEN
        $this->client->request('GET', '/profile');

        // THEN
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.profile-username', 'Jan Kowalski');
        $this->assertSelectorTextContains('.profile-handle', '@'.$this->user->getUsername());
        $this->assertSelectorTextContains('.profile-field-value', $this->user->getEmail());
    }
}
