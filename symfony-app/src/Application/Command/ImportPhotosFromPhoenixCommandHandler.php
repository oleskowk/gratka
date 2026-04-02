<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\Exception\UserNotFoundException;
use App\Domain\Exception\PhoenixTokenMissingException;
use App\Domain\Model\Photo;
use App\Domain\Port\PhoenixApiClientInterface;
use App\Domain\Port\PhotoFindRepositoryInterface;
use App\Domain\Port\PhotoSaveRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use App\Infrastructure\Security\EncryptionServiceInterface;
use Psr\Log\LoggerInterface;

final class ImportPhotosFromPhoenixCommandHandler
{
    public function __construct(
        private PhoenixApiClientInterface $phoenixApiClient,
        private UserReadRepositoryInterface $userRepository,
        private PhotoFindRepositoryInterface $photoFindRepository,
        private PhotoSaveRepositoryInterface $photoSaveRepository,
        private EncryptionServiceInterface $encryptionService,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImportPhotosFromPhoenixCommand $command): void
    {
        $this->logger->info('Starting photo import from Phoenix', ['userId' => $command->userId]);

        $user = $this->userRepository->findById($command->userId);
        if (null === $user) {
            $this->logger->error('User not found for photo import', ['userId' => $command->userId]);

            throw new UserNotFoundException();
        }

        $encryptedToken = $user->getPhoenixApiToken();
        if (null === $encryptedToken) {
            $this->logger->warning('No Phoenix API token saved for user, cannot import photos', ['userId' => $command->userId]);

            throw new PhoenixTokenMissingException($command->userId);
        }

        try {
            $token = $this->encryptionService->decrypt($encryptedToken);
        } catch (\Exception $e) {
            $this->logger->error('Failed to decrypt Phoenix API token', [
                'userId' => $command->userId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }

        $externalPhotos = $this->phoenixApiClient->fetchPhotos($token);

        $importedCount = 0;
        foreach ($externalPhotos as $externalPhoto) {
            $externalId = $externalPhoto->externalId;
            $photoUrl = $externalPhoto->url;

            if (null !== $this->photoFindRepository->findByExternalId($externalId, 'phoenix')) {
                $this->logger->debug('Photo already imported (externalId exists), skipping', ['externalId' => $externalId]);

                continue;
            }

            $photo = new Photo();
            $photo->setUser($user);
            $photo->setImageUrl($photoUrl);
            $photo->setExternalId($externalId);
            $photo->setSource('phoenix');

            $this->photoSaveRepository->save($photo);
            ++$importedCount;
        }

        if ($importedCount > 0) {
            $this->photoSaveRepository->flush();
        }

        $this->logger->info('Finished photo import from Phoenix', [
            'userId' => $command->userId,
            'importedCount' => $importedCount,
        ]);
    }
}
