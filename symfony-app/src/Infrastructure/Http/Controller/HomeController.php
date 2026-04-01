<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Query\GetHomepageQuery;
use App\Application\Query\GetHomepageQueryHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly GetHomepageQueryHandler $queryHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $userIdValue = $request->getSession()->get('user_id');
        $userId = is_scalar($userIdValue) ? (int) $userIdValue : null;

        $this->logger->debug('Home page requested', ['userId' => $userId]);

        $view = ($this->queryHandler)(
            new GetHomepageQuery(currentUserId: $userId),
        );

        return $this->render('home/index.html.twig', [
            'photos' => $view->photos,
            'currentUser' => $view->currentUser,
            'userLikes' => $view->userLikes,
        ]);
    }
}
