<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\File\Interface\FileBuilderInterface;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractFileBuilder implements FileBuilderInterface
{
    protected string $content;

    public function __construct(private readonly SpinnerFilePath $path, protected readonly FilePathCollection $filePaths)
    {
    }

    abstract public function build(InputInterface $input): void;

    public function save(): void
    {
        file_put_contents($this->path->getAbsolutePath(), $this->content);
    }


    protected function addNewLine(): void
    {
        $this->content .= "\r\n\r\n";
    }
}