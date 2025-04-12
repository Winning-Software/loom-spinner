<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Collection;

use Loom\Spinner\Classes\File\SpinnerFilePath;
use Loom\Utility\Collection\AbstractCollection;

class FilePathCollection extends AbstractCollection
{
    public function __construct(array $items = [])
    {
        $items = array_filter($items, function ($item) {
            return $item instanceof SpinnerFilePath;
        });

        parent::__construct($items);
    }

    public function get(string $key): ?SpinnerFilePath
    {
        return $this->items[$key] ?? null;
    }
}