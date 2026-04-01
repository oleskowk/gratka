<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Command\ToggleLikeCommand;
use App\Application\Command\ToggleLikeCommandHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private ToggleLikeCommandHandler $commandHandler,
    ) {
    }

    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function like(int $id, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!is_scalar($userId)) {
            $this->addFlash('error', 'You must be logged in to like photos.');

            return $this->redirectToRoute('home');
        }

        try {
            ($this->commandHandler)(new ToggleLikeCommand(userId: (int) $userId, photoId: $id));
        } catch (NotFoundHttpException $e) {
            throw $e;
        }

        $this->addFlash('success', 'Done!');

        return $this->redirectToRoute('home');
    }
}
