<?php

declare(strict_types=1);

namespace Loom\Spinner\Classes\File;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Input\InputInterface;

class DockerComposeFileBuilder extends AbstractFileBuilder
{
    /**
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $projectDockerCompose = $config->getFilePaths()->get('projectDockerCompose');

        if (!$projectDockerCompose instanceof SpinnerFilePath) {
            throw new \Exception('Project Docker Compose file path not found.');
        }

        return parent::__construct($projectDockerCompose, $config);
    }

    public function build(InputInterface $input): DockerComposeFileBuilder
    {
        $this->content = file_get_contents(
            $this->config->getFilePaths()->get('phpYamlTemplate')->getAbsolutePath()
        );

        return $this;
    }
}