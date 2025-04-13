<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\Collection;

use Loom\Utility\Collection\AbstractCollection;
use Loom\Utility\FilePath\FilePath;

class FilePathCollection extends AbstractCollection
{
    public function __construct(array $items = [])
    {
        $items = array_filter($items, function ($item) {
            return $item instanceof FilePath;
        });

        parent::__construct($items);
    }

    public function get(string $key): ?FilePath
    {
        return $this->items[$key] ?? null;
    }
}