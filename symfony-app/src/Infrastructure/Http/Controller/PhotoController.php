<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Command\ImportPhotosFromPhoenixCommand;
use App\Application\Command\ImportPhotosFromPhoenixCommandHandler;
use App\Application\Command\ToggleLikeCommand;
use App\Application\Command\ToggleLikeCommandHandler;
use App\Application\Command\UpdatePhoenixTokenCommand;
use App\Application\Command\UpdatePhoenixTokenCommandHandler;
use App\Infrastructure\ExternalApi\Exception\InvalidPhoenixTokenException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private ToggleLikeCommandHandler $commandHandler,
        private ImportPhotosFromPhoenixCommandHandler $importPhotosHandler,
        private UpdatePhoenixTokenCommandHandler $updateTokenHandler,
    ) {
    }

    #[Route('/photos/import', name: 'photos_import', methods: ['POST'])]
    public function importPhotos(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (! is_scalar($userId)) {
            $this->addFlash('error', 'You must be logged in to import photos.');

            return $this->redirectToRoute('home');
        }

        try {
            ($this->importPhotosHandler)(new ImportPhotosFromPhoenixCommand((int) $userId));
            $this->addFlash('success', 'Photos imported successfully!');
        } catch (InvalidPhoenixTokenException $e) {
            $this->addFlash('error', 'The Phoenix API token is invalid. Please update it in your profile.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred during import: '.$e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/token/update', name: 'profile_token_update', methods: ['POST'])]
    public function updatePhoenixToken(Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (! is_scalar($userId)) {
            $this->addFlash('error', 'You must be logged in to update your token.');

            return $this->redirectToRoute('home');
        }

        $token = $request->request->get('token');
        if (empty($token)) {
            $this->addFlash('error', 'Token cannot be empty.');

            return $this->redirectToRoute('profile');
        }

        try {
            ($this->updateTokenHandler)(new UpdatePhoenixTokenCommand((int) $userId, (string) $token));
            $this->addFlash('success', 'Phoenix API token updated successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred when saving the token: '.$e->getMessage());
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function like(int $id, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (! is_scalar($userId)) {
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
