<?php

declare(strict_types=1);

namespace App\Maintenance\PlanImport;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

final class PlanImportFileStorage
{
    public function __construct(
        private readonly string $planImportStorageDir,
    ) {
    }

    public function store(UploadedFile $file): string
    {
        if (!is_dir($this->planImportStorageDir) && !mkdir($this->planImportStorageDir, 0775, true) && !is_dir($this->planImportStorageDir)) {
            throw new \RuntimeException('Не вдалося створити каталог для імпортів.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $relative = sprintf('%s.%s', Uuid::v7()->toRfc4122(), $extension);
        $file->move($this->planImportStorageDir, $relative);

        return $relative;
    }

    public function absolutePath(string $relativePath): string
    {
        return $this->planImportStorageDir.'/'.$relativePath;
    }
}
