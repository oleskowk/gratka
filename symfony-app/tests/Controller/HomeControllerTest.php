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
    private Photo $photo;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        $username = uniqid('u_');
        $this->user = new User();
        $this->user
            ->setUsername($username)
            ->setEmail($username.'@a.com')
            ->setName('Jan')
            ->setLastName('Kowalski')
            ->setAge(20)
            ->setBio('Z');

        $this->photo = new Photo();
        $this->photo
            ->setImageUrl('http://test.com/img.jpg')
            ->setLocation('Tatry')
            ->setDescription('Piękny krajobraz')
            ->setCamera('Canon')
            ->setTakenAt(new \DateTimeImmutable())
            ->setUser($this->user);

        $this->authToken = new AuthToken();
        $this->authToken
            ->setToken('test-token-'.uniqid())
            ->setUser($this->user);

        $this->entityManager->persist($this->user);
        $this->entityManager->persist($this->photo);
        $this->entityManager->persist($this->authToken);
        $this->entityManager->flush();
    }

    #[Test]
    public function itRendersHomepageForAnonymousUser(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.photo-card');
        $this->assertSelectorExists('img[src="'.$this->photo->getImageUrl().'"]');
        $this->assertSelectorTextContains('.photo-description', $this->photo->getDescription());
        $this->assertSelectorTextContains('.author-name', 'Jan Kowalski');
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

        $this->client->request('GET', '/photo/'.$this->photo->getId().'/like');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.photo-card .like-button.liked');
        $this->assertSelectorTextContains('.like-button.liked span', '1');
        $this->assertSelectorTextContains(
            '.photo-card:first-child .photo-description',
            $this->photo->getDescription()
        );
    }
}
