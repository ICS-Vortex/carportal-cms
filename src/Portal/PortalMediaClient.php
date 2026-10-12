<?php

declare(strict_types=1);

namespace App\Portal;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PortalMediaClient
{
    private const array ALLOWED_MIME = [
        'image/jpeg' => true,
        'image/png' => true,
        'image/webp' => true,
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(default::PORTAL_API_INTERNAL_URL)%')]
        private readonly string $apiBaseUrl,
        #[Autowire('%env(default::CMS_UPLOAD_SECRET)%')]
        private readonly string $uploadSecret,
    ) {
    }

    public function uploadServiceStationImage(UploadedFile $file): string
    {
        $mime = (string) $file->getMimeType();
        if (!isset(self::ALLOWED_MIME[$mime])) {
            throw new \InvalidArgumentException('Підтримуються лише JPG, PNG та WEBP.');
        }

        $contents = (string) file_get_contents($file->getPathname());
        $dataUrl = sprintf('data:%s;base64,%s', $mime, base64_encode($contents));

        return $this->postJson('/v1/cms/service-station-images', ['dataUrl' => $dataUrl])['url']
            ?? throw new \RuntimeException('Portal API не повернув URL зображення.');
    }

    public function deleteManagedImage(?string $url): void
    {
        if ($url === null || $url === '') {
            return;
        }

        $this->postJson('/v1/cms/managed-images/delete', ['url' => $url]);
    }

    /**
     * @return array<string, mixed>
     */
    private function postJson(string $path, array $payload): array
    {
        if ($this->uploadSecret === '') {
            throw new \RuntimeException('CMS_UPLOAD_SECRET не налаштовано.');
        }

        $base = rtrim($this->apiBaseUrl, '/');
        $response = $this->httpClient->request('POST', $base.$path, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Cms-Upload-Key' => $this->uploadSecret,
            ],
            'json' => $payload,
        ]);

        $status = $response->getStatusCode();
        $body = $response->toArray(false);

        if ($status < 200 || $status >= 300) {
            $message = is_array($body) && isset($body['error']['message']) && is_string($body['error']['message'])
                ? $body['error']['message']
                : 'Не вдалося звернутися до Portal API.';
            throw new \RuntimeException($message);
        }

        if (!is_array($body) || !isset($body['data']) || !is_array($body['data'])) {
            return is_array($body) ? $body : [];
        }

        return $body['data'];
    }
}
