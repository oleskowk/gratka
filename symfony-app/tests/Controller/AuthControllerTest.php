<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Domain\Model\AuthToken;
use App\Domain\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private User $defaultUser;
    private string $defaultToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $this->defaultToken = 'test-token-'.uniqid();
        $this->defaultUser = $this->createPersistedUser('test_'.uniqid());
        $this->createPersistedToken($this->defaultUser, $this->defaultToken);
    }

    #[Test]
    public function itSuccessfullyLogsInUserWithValidTokenAndUsername(): void
    {
        // GIVEN
        $this->givenUserIsAuthenticated();

        // THEN
        $this->assertResponseRedirects('/');
        $this->assertIsAuthenticatedAs($this->defaultUser);
    }

    #[Test]
    public function itReturns401ForInvalidToken(): void
    {
        // WHEN
        $url = sprintf('/auth/%s/invalid_token', $this->defaultUser->getUsername());
        $this->client->request('GET', $url);

        // THEN
        $this->assertResponseStatusCodeSame(401);
        $this->assertStringContainsString('Invalid token', $this->client->getResponse()->getContent());
        $this->assertIsNotAuthenticated();
    }

    #[Test]
    public function itReturns404ForValidTokenButInvalidUser(): void
    {
        // WHEN
        $url = sprintf('/auth/non_existent_user/%s', $this->defaultToken);
        $this->client->request('GET', $url);

        // THEN
        $this->assertResponseStatusCodeSame(404);
        $this->assertStringContainsString('User not found', $this->client->getResponse()->getContent());
        $this->assertIsNotAuthenticated();
    }

    #[Test]
    public function itLogsOutUserAndClearsSession(): void
    {
        // GIVEN
        $this->givenUserIsAuthenticated();

        // WHEN
        $this->client->request('GET', '/logout');

        // THEN
        $this->assertResponseRedirects('/');
        $this->assertIsNotAuthenticated();
    }

    private function givenUserIsAuthenticated(): void
    {
        $url = sprintf('/auth/%s/%s', $this->defaultUser->getUsername(), $this->defaultToken);
        $this->client->request('GET', $url);
    }

    private function createPersistedUser(string $username): User
    {
        $user = new User();
        $user->setUsername($username)
             ->setEmail($username.'@example.com')
             ->setName('Test')
             ->setLastName('User')
             ->setAge(30)
             ->setBio('Test Bio');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createPersistedToken(User $user, string $tokenValue): void
    {
        $token = new AuthToken();
        $token->setToken($tokenValue)->setUser($user);

        $this->entityManager->persist($token);
        $this->entityManager->flush();
    }

    private function assertIsAuthenticatedAs(User $user): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.profile-menu', 'User should see profile menu when logged in.');
        $this->assertSelectorExists('a[href="/logout"]', 'User should see logout link when logged in.');
    }

    private function assertIsNotAuthenticated(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.profile-menu', 'User should NOT see profile menu when anonymous.');
        $this->assertSelectorNotExists('a[href="/logout"]', 'User should NOT see logout link when anonymous.');
    }
}
