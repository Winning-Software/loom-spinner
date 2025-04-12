<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\File\SpinnerFilePath;
use Loom\Spinner\Classes\OS\System;
use Loom\Spinner\Command\Interface\ConsoleCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class AbstractSpinnerCommand extends Command implements ConsoleCommandInterface
{
    protected SymfonyStyle $style;
    protected System $system;
    protected FilePathCollection $filePaths;
    protected string $rootDirectory;

    public function __construct()
    {
        $this->rootDirectory = dirname(__DIR__, 2);
        $this->system = new System();
        $this->setFilePaths();

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setStyle($input, $output);

        $this->style->title('Loom Spinner');

        if (!$this->system->isDockerEngineRunning()) {
            $this->style->error('It looks like the Docker Engine is not running. Please start it and try again.');

            return Command::FAILURE;
        }

        if ($input->hasArgument('path')) {
            $projectDirectory = new SpinnerFilePath($input->getArgument('path'));

            if (!$projectDirectory->exists() || !$projectDirectory->isDirectory()) {
                $this->style->error('The provided path is not a valid directory.');

                return Command::FAILURE;
            }

            $this->filePaths->add($projectDirectory, 'project');
        }

        if ($input->hasArgument('name')) {
            $this->filePaths->add(
                new SpinnerFilePath(sprintf('data/environments/%s', $input->getArgument('name'))),
                'projectData'
            );
            $this->filePaths->add(
                new SpinnerFilePath(sprintf('data/environments/%s/.env', $input->getArgument('name'))),
                'projectEnv'
            );
            $this->filePaths->add(
                new SpinnerFilePath(sprintf('data/environments/%s/docker-compose.yml', $input->getArgument('name'))),
                'projectDockerCompose'
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function getDefaultConfig()
    {
        if (!$this->filePaths->get('defaultSpinnerConfig')?->exists()) {
            throw new \Exception('Default spinner configuration file not found.');
        }

        return Yaml::parseFile($this->filePaths->get('defaultSpinnerConfig')->getAbsolutePath())['options'] ?? null;
    }

    protected function buildDockerComposeCommand(string $command, bool $daemon = true): string
    {
        return sprintf(
            'cd %s && docker-compose --env-file=%s %s%s',
            $this->filePaths->get('projectData')->getAbsolutePath(),
            $this->filePaths->get('projectEnv')->getAbsolutePath(),
            $command,
            $daemon ? ' -d' : ''
        );
    }

    /**
     * @throws \Exception
     */
    protected function getDefaultPhpVersion(): ?float
    {
        if (!$this->filePaths->get('defaultSpinnerConfig')?->exists()) {
            throw new \Exception('Default spinner configuration file not found.');
        }

        $config = $this->getDefaultConfig();

        return $config['environment']['php']['version'] ?? null;
    }

    private function setStyle(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    private function setFilePaths(): void
    {
        $this->filePaths = new FilePathCollection([
            'config' => new SpinnerFilePath('config'),
            'defaultSpinnerConfig' => new SpinnerFilePath('config/spinner.yaml'),
            'envTemplate' => new SpinnerFilePath('config/.template.env'),
            'data' => new SpinnerFilePath('data'),
            'phpYamlTemplate' => new SpinnerFilePath('config/php.yaml'),
            'phpFpmDockerfileTemplate' => new SpinnerFilePath('config/php-fpm/Dockerfile'),
            'phpFpmDataDirectory' => new SpinnerFilePath('config/php-fpm'),
        ]);
    }
}