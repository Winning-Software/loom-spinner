<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File\Interface;

use Symfony\Component\Console\Input\InputInterface;

interface FileBuilderInterface
{
    public function build(InputInterface $input): void;
    public function save(): void;
}