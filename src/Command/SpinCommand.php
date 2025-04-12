<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\OS\PortGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'spin:up', description: 'Spin up a new development environment')]
class SpinCommand extends AbstractSpinnerCommand
{
    private PortGenerator $portGenerator;
    private array $ports;

    public function __construct()
    {
        $this->portGenerator = new PortGenerator();
        $this->ports = [
            'php' => $this->portGenerator->generateRandomPort(),
        ];

        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name for your Docker container.')
            ->addArgument('path', InputArgument::REQUIRED, 'The absolute path to your projects root directory.')
            ->addOption('php', null, InputOption::VALUE_REQUIRED, 'The PHP version to use (e.g., 8.0).', $this->getDefaultPhpVersion());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        if ($this->filePaths->get('projectData')->exists()) {
            $this->style->warning('Project data already exists. Skipping new build.');

            return Command::SUCCESS;
        }

        $this->style->success("Spinning up a new development environment...");

        $this->createProjectData($input);

        $command = $this->buildDockerComposeCommand(sprintf('-p %s up', $input->getArgument('name')));

        passthru($command);

        return Command::SUCCESS;
    }

    private function createProjectData(InputInterface $input): void
    {
        mkdir($this->filePaths->get('projectData')->getProvidedPath(), 0777, true);

        $this->createEnvironmentFile($input);
        $this->buildDockerComposeFile($input);
    }

    private function createEnvironmentFile(InputInterface $input): void
    {
        file_put_contents(
            $this->filePaths->get('projectEnv')->getProvidedPath(),
            sprintf(
                file_get_contents($this->filePaths->get('envTemplate')->getAbsolutePath()),
                $this->filePaths->get('project')->getAbsolutePath(),
                $input->getArgument('name'),
                $input->getOption('php'),
                $this->getPort('php'),
            )
        );
    }

    private function buildDockerComposeFile(InputInterface $input): void
    {
        if (!file_exists($this->filePaths->get('projectData')->getProvidedPath() . '/php-fpm')) {
            mkdir($this->filePaths->get('projectData')->getProvidedPath() . '/php-fpm', 0777, true);
        }
        file_put_contents(
            $this->filePaths->get('projectDockerCompose')->getProvidedPath(),
            file_get_contents($this->filePaths->get('phpYamlTemplate')->getAbsolutePath())
        );
        $phpFpmDockerfileTemplate = file_get_contents($this->filePaths->get('phpFpmDockerfileTemplate')->getAbsolutePath());
        $phpFpmDockerfileTemplate = str_replace('${PHP_VERSION}', $input->getOption('php'), $phpFpmDockerfileTemplate);

        file_put_contents(
            $this->filePaths->get('projectData')->getProvidedPath() . '/php-fpm/Dockerfile',
            $phpFpmDockerfileTemplate
        );
    }

    private function getPort(string $service): int
    {
        return $this->ports[$service] ?? $this->portGenerator->generateRandomPort();
    }
}