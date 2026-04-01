<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Exception\InvalidTokenException;
use App\Application\Exception\UserNotFoundException;
use App\Application\Query\AuthenticateUserQuery;
use App\Application\Query\AuthenticateUserQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthenticateUserQueryHandler $authenticateUserHandler
    ) {
    }

    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function login(string $username, string $token, Request $request): Response
    {
        try {
            $user = ($this->authenticateUserHandler)(new AuthenticateUserQuery($username, $token));

            $session = $request->getSession();
            $session->set('user_id', $user->id);
            $session->set('username', $user->username);

            $this->addFlash('success', 'Welcome back, ' . $user->username . '!');

            return $this->redirectToRoute('home');
        } catch (InvalidTokenException $e) {
            return new Response($e->getMessage(), 401);
        } catch (UserNotFoundException $e) {
            return new Response($e->getMessage(), 404);
        }
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
