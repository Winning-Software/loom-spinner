<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Loom\Spinner\Classes\File\Interface\FileBuilderInterface;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractFileBuilder implements FileBuilderInterface
{
    protected string $content;

    public function __construct(protected string $path, protected Config $config)
    {
        return $this;
    }

    abstract public function build(InputInterface $input): AbstractFileBuilder;

    public function save(): void
    {
        file_put_contents($this->path, $this->content);
    }


    protected function addNewLine(): void
    {
        $this->content .= "\r\n\r\n";
    }
}