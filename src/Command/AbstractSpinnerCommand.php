<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\Config\Config;
use Loom\Spinner\Classes\File\SpinnerFilePath;
use Loom\Spinner\Classes\OS\System;
use Loom\Spinner\Command\Interface\ConsoleCommandInterface;
use Loom\Utility\FilePath\FilePath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class AbstractSpinnerCommand extends Command implements ConsoleCommandInterface
{
    protected Config $config;
    protected FilePathCollection $filePaths;
    protected string $rootDirectory;
    protected SymfonyStyle $style;
    protected System $system;

    public function __construct()
    {
        $this->rootDirectory = dirname(__DIR__, 2);
        $this->system = new System();
        $this->setFilePaths();
        $this->config = new Config($this->filePaths);

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

        if (!$this->validatePathArgument($input)) {
            return Command::FAILURE;
        }

        $this->validateNameArgument($input);

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
    protected function getEnvironmentOption(string $service, string $option): mixed
    {
        if ($this->filePaths->get('projectCustomConfig')?->exists()) {
            $config = Yaml::parseFile(
                $this->filePaths->get('projectCustomConfig')->getAbsolutePath()
            )['options']['environment'] ?? null;

            if ($config) {
                if (isset($config[$service][$option])) {
                    return $config[$service][$option];
                }
            }
        }

        return $this->getDefaultConfig()['environment'][$service][$option] ?? null;
    }

    /**
     * @throws \Exception
     */
    protected function isNodeEnabled(InputInterface $input): bool
    {
        if ($input->getOption('node-disabled')) {
            return true;
        }

        return $this->getEnvironmentOption('node', 'enabled');
    }

    private function validatePathArgument(InputInterface $input): bool
    {
        if ($input->hasArgument('path')) {
            $projectDirectory = new FilePath($input->getArgument('path'));

            if (!$projectDirectory->exists() || !$projectDirectory->isDirectory()) {
                $this->style->error('The provided path is not a valid directory.');

                return false;
            }

            $this->filePaths->add($projectDirectory, 'project');
        }

        return true;
    }

    private function validateNameArgument(InputInterface $input): void
    {
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
            $this->filePaths->add(
                new FilePath($this->filePaths->get('project')->getAbsolutePath() . DIRECTORY_SEPARATOR . 'spinner.yaml'),
                'projectCustomConfig'
            );
        }
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
            'phpFpmDataDirectory' => new SpinnerFilePath('config/php-fpm'),
            'phpFpmDockerfileTemplate' => new SpinnerFilePath('config/php-fpm/Dockerfile'),
            'nodeDockerfileTemplate' => new SpinnerFilePath('config/php-fpm/Node.Dockerfile'),
        ]);
    }
}