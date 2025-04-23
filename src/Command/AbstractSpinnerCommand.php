<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Collection\FilePathCollection;
use Loom\Spinner\Classes\Config\Config;
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
    protected SymfonyStyle $style;
    protected System $system;

    public function __construct()
    {
        $this->system = new System();
        $this->config = new Config();

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

    protected function buildDockerComposeCommand(string $command, bool $daemon = true): string
    {
        return sprintf(
            'cd %s && docker compose --env-file=%s %s%s',
            $this->config->getFilePaths()->get('projectData')->getAbsolutePath(),
            $this->config->getFilePaths()->get('projectEnv')->getAbsolutePath(),
            $command,
            $daemon ? ' -d' : ''
        );
    }

    private function validatePathArgument(InputInterface $input): bool
    {
        if ($input->hasArgument('path')) {
            $projectDirectory = new FilePath($input->getArgument('path'));

            if (!$projectDirectory->exists() || !$projectDirectory->isDirectory()) {
                $this->style->error('The provided path is not a valid directory.');

                return false;
            }

            $this->config->addFilePath($projectDirectory, 'project');
        }

        return true;
    }

    private function validateNameArgument(InputInterface $input): void
    {
        var_dump('Validating name argument');
        if ($input->hasArgument('name')) {
            $this->config->addFilePath(
                new FilePath(sprintf('data/environments/%s', $input->getArgument('name'))),
                'projectData'
            );
            $this->config->addFilePath(
                new FilePath(sprintf('data/environments/%s/.env', $input->getArgument('name'))),
                'projectEnv'
            );
            $this->config->addFilePath(
                new FilePath(sprintf('data/environments/%s/docker-compose.yml', $input->getArgument('name'))),
                'projectDockerCompose'
            );
            $this->config->addFilePath(
                new FilePath(sprintf('data/environments/%s/php-fpm/Dockerfile', $input->getArgument('name'))),
                'projectPhpFpmDockerfile'
            );
            $this->config->addFilePath(
                new FilePath(sprintf('data/environments/%s/nginx/Dockerfile', $input->getArgument('name'))),
                'projectNginxDockerfile'
            );
            $this->config->addFilePath(
                new FilePath($this->config->getFilePaths()->get('project')->getAbsolutePath() . DIRECTORY_SEPARATOR . 'spinner.yaml'),
                'projectCustomConfig'
            );
        }
    }

    private function setStyle(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }
}