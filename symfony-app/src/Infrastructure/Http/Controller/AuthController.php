<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Exception\InvalidTokenException;
use App\Application\Exception\UserNotFoundException;
use App\Application\Query\AuthenticateUserQuery;
use App\Application\Query\AuthenticateUserQueryHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthenticateUserQueryHandler $authenticateUserHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function login(string $username, string $token, Request $request): Response
    {
        $this->logger->info('Authentication request received', ['username' => $username]);

        try {
            $user = ($this->authenticateUserHandler)(new AuthenticateUserQuery($username, $token));

            $session = $request->getSession();
            $session->set('user_id', $user->id);
            $session->set('username', $user->username);

            $this->addFlash('success', 'Welcome back, '.$user->username.'!');

            $this->logger->info('User successfully logged in via token', ['username' => $username, 'userId' => $user->id]);

            return $this->redirectToRoute('home');
        } catch (InvalidTokenException|UserNotFoundException $e) {
            $this->logger->warning('Authentication failed at controller level', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return new Response($e->getMessage(), $e instanceof InvalidTokenException ? 401 : 404);
        }
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $this->logger->info('User logging out', ['userId' => $session->get('user_id')]);
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
