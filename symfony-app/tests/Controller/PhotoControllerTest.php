<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Domain\Model\AuthToken;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Infrastructure\ExternalApi\Dto\PhoenixPhotoDto;
use App\Infrastructure\ExternalApi\Mock\PhoenixApiMock;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PhotoControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    #[Test]
    public function itRedirectsAnonymousUserWhenTryingToLikePhoto(): void
    {
        // WHEN
        $this->client->request('GET', '/photo/999/like');

        // THEN
        $this->assertResponseRedirects('/');
    }

    #[Test]
    public function itThrows404WhenLikingNonExistentPhoto(): void
    {
        // GIVEN
        $user = $this->createPersistedUser('test_user');
        $this->authenticateAs($user);

        // WHEN
        $this->client->request('GET', '/photo/999999/like');

        // THEN
        $this->assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function itLikesPhotoWhenLoggedIn(): void
    {
        // GIVEN
        $user = $this->createPersistedUser('user');
        $photoOwner = $this->createPersistedUser('owner');
        $photo = $this->createPersistedPhoto($photoOwner);

        $this->authenticateAs($user);

        // Check it's not liked initially on the page
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists(sprintf('a.like-button[href="/photo/%d/like"].liked', $photo->getId()));

        // WHEN
        $this->client->request('GET', sprintf('/photo/%d/like', $photo->getId()));

        // THEN
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // Assert it is now liked on the page
        $this->assertSelectorExists(sprintf('a.like-button[href="/photo/%d/like"].liked', $photo->getId()));
        $this->assertSelectorTextContains(sprintf('a.like-button[href="/photo/%d/like"]', $photo->getId()), '❤️');
    }

    #[Test]
    public function itUnlikesPhotoIfAlreadyLiked(): void
    {
        // GIVEN
        $user = $this->createPersistedUser('user');
        $photoOwner = $this->createPersistedUser('owner');
        $photo = $this->createPersistedPhoto($photoOwner);

        $this->authenticateAs($user);
        // Pre-like it
        $this->client->request('GET', sprintf('/photo/%d/like', $photo->getId()));

        // Pre-verify it is liked initially on the page
        $this->client->request('GET', '/');
        $this->assertSelectorExists(sprintf('a.like-button[href="/photo/%d/like"].liked', $photo->getId()));

        // WHEN
        $this->client->request('GET', sprintf('/photo/%d/like', $photo->getId()));

        // THEN
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // Assert it is now unliked on the page
        $this->assertSelectorNotExists(sprintf('a.like-button[href="/photo/%d/like"].liked', $photo->getId()));
        $this->assertSelectorTextContains(sprintf('a.like-button[href="/photo/%d/like"]', $photo->getId()), '🤍');
    }

    private function createPersistedUser(string $prefix): User
    {
        $user = new User();
        $user->setUsername(uniqid($prefix.'_'))
             ->setEmail(uniqid().'@example.com')
             ->setName('Test')
             ->setLastName('User')
             ->setAge(20)
             ->setBio('Bio');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createPersistedPhoto(User $owner): Photo
    {
        $photo = new Photo();
        $photo->setImageUrl('http://test.com/img.jpg')
              ->setLocation('Loc')
              ->setDescription('Desc')
              ->setCamera('Cam')
              ->setTakenAt(new \DateTimeImmutable())
              ->setUser($owner);

        $this->entityManager->persist($photo);
        $this->entityManager->flush();

        return $photo;
    }

    private function authenticateAs(User $user): void
    {
        $tokenValue = 'test-token-'.uniqid();
        $token = new AuthToken();
        $token->setToken($tokenValue)->setUser($user);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->client->request('POST', '/auth/login', [
            'username' => $user->getUsername(),
            'token' => $tokenValue,
        ]);
        $this->client->followRedirect();
    }

    #[Test]
    public function itSuccessfullyUpdatesPhoenixApiToken(): void
    {
        // GIVEN
        $user = $this->createPersistedUser('token_user');
        $this->authenticateAs($user);

        // WHEN
        $this->client->request('POST', '/profile/token/update', [
            'token' => 'new-phoenix-token-123',
        ]);

        // THEN
        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertStringContainsString('Phoenix API token updated successfully!', $this->client->getResponse()->getContent());

        // Refresh user from DB
        $this->entityManager->clear();
        $user = $this->entityManager->find(User::class, $user->getId());
        $this->assertSame('new-phoenix-token-123', $user->getPhoenixApiToken());
    }

    #[Test]
    public function itImportsPhotosSuccessfullyViaController(): void
    {
        // GIVEN
        $user = $this->createPersistedUser('import_user');
        $this->authenticateAs($user);

        // Update token via endpoint (not direct DB access)
        $this->client->request('POST', '/profile/token/update', ['token' => 'mock-api-token']);
        $this->client->followRedirect();

        // Mock the Phoenix API response via our test mock service
        /** @var PhoenixApiMock $mockClient */
        $mockClient = static::getContainer()->get(PhoenixApiMock::class);
        $mockClient->setPhotos([
            new PhoenixPhotoDto('ext-new-functional-1', 'https://example.com/functional1.jpg'),
        ]);

        // WHEN
        $this->client->request('POST', '/photos/import');

        // THEN
        $this->assertResponseRedirects('/profile');
        $this->client->followRedirect();
        $this->assertStringContainsString('Photos imported successfully!', $this->client->getResponse()->getContent());

        // Verify photo exists in DB
        $photoRepository = $this->entityManager->getRepository(Photo::class);
        $photo = $photoRepository->findOneBy(['externalId' => 'ext-new-functional-1']);
        $this->assertNotNull($photo, 'Photo should be saved in DB with correct externalId');
        $this->assertSame('https://example.com/functional1.jpg', $photo->getImageUrl());
    }
}
