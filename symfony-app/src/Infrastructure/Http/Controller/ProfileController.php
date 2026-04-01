<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Query\GetProfileQuery;
use App\Application\Query\GetProfileQueryHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly GetProfileQueryHandler $queryHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        if (!is_scalar($userId)) {
            $this->logger->info('Redirecting to home: no user_id in session or invalid');
            return $this->redirectToRoute('home');
        }

        $view = ($this->queryHandler)(new GetProfileQuery(userId: (int) $userId));

        if (null === $view) {
            $this->logger->warning('User not found for profile, clearing session', ['userId' => $userId]);
            $session->clear();

            return $this->redirectToRoute('home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $view->user,
        ]);
    }
}
