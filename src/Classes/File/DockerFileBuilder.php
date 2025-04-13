<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Symfony\Component\Console\Input\InputInterface;

class DockerFileBuilder extends AbstractFileBuilder
{
    public function __construct(FilePathCollection $filePaths)
    {
        $projectDataDir = $filePaths->get('projectData');

        if (!$projectDataDir instanceof SpinnerFilePath) {
            return;
        }

        parent::__construct($projectDataDir, $filePaths);
    }

    public function build(InputInterface $input): void
    {
        $this->content = file_get_contents($this->filePaths->get('phpFpmDockerfileTemplate')->getAbsolutePath());
        $this->content = str_replace('${PHP_VERSION}', $input->getOption('php'), $this->content);
    }
}