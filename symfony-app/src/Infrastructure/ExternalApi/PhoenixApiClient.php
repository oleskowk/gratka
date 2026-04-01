<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi;

use App\Domain\Port\PhoenixApiClientInterface;
use App\Infrastructure\ExternalApi\Exception\InvalidPhoenixTokenException;
use App\Infrastructure\ExternalApi\Exception\PhoenixApiResponseException;
use App\Infrastructure\ExternalApi\Factory\PhoenixPhotoFactory;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhoenixApiClient implements PhoenixApiClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private PhoenixPhotoFactory $photoFactory,
        private string $phoenixBaseUrl,
    ) {
    }

    public function fetchPhotos(string $apiToken): array
    {
        $url = sprintf('%s/api/photos', $this->phoenixBaseUrl);
        $this->logger->info('Fetching photos from Phoenix API', ['url' => $url]);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'access-token' => $apiToken,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if (401 === $statusCode || 403 === $statusCode) {
                $this->logger->warning('Phoenix API authentication failed (401/403)');

                throw new InvalidPhoenixTokenException();
            }

            if (200 !== $statusCode) {
                $this->logger->error('Phoenix API returned non-200 status code', [
                    'status_code' => $statusCode,
                ]);

                throw new PhoenixApiResponseException($statusCode);
            }

            $data = $response->toArray();
            $photos = $this->photoFactory->createCollectionFromApiArray($data);

            $this->logger->info('Successfully fetched photos from Phoenix API', [
                'count' => count($photos),
            ]);

            return $photos;
        } catch (\Exception $e) {
            $this->logger->critical('Exception occurred during Phoenix API call', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
