<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Utility\FilePath\FilePath;

class SpinnerFilePath extends FilePath
{
    public function __construct(string $path)
    {
        parent::__construct(sprintf('%s%s%s', dirname(__DIR__, 3), DIRECTORY_SEPARATOR, $path));
    }

    public function getProvidedPath(): string
    {
        return $this->path;
    }
}