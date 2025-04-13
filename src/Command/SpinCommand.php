<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\File\SpinnerFilePath;
use Loom\Spinner\Classes\OS\PortGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addOption('php', null, InputOption::VALUE_REQUIRED, 'The PHP version to use (e.g., 8.0).', $this->config->getDefaultPhpVersionArgument())
            ->addOption('node-disabled', null, InputOption::VALUE_NONE, 'Set this flag to disable Node.js for your environment.');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        if ($this->projectDataExists()) {
            return Command::SUCCESS;
        }

        $this->style->success("Spinning up a new development environment...");

        $this->createProjectData($input);

        $command = $this->buildDockerComposeCommand(sprintf('-p %s up', $input->getArgument('name')));

        passthru($command);

        return Command::SUCCESS;
    }

    protected function projectDataExists(): bool
    {
        if (!$this->filePaths->get('projectData')->exists()) {
            $this->style->warning('Project data already exists. Skipping new build.');

            return false;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    private function createProjectData(InputInterface $input): void
    {
        $this->createProjectDataDirectory();
        $this->createEnvironmentFile($input);
        $this->buildDockerComposeFile();
        $this->buildDockerfile($input);
    }

    /**
     * @throws \Exception
     */
    private function createProjectDataDirectory(): void
    {
        $projectData = $this->filePaths->get('projectData');

        if (!$projectData instanceof  SpinnerFilePath) {
            throw new \Exception('Invalid project data directory provided.');
        }

        mkdir($projectData->getProvidedPath(), 0777, true);
    }

    /**
     * @throws \Exception
     */
    private function createEnvironmentFile(InputInterface $input): void
    {
        $projectEnv = $this->filePaths->get('projectEnv');

        if (!$projectEnv instanceof SpinnerFilePath) {
            throw new \Exception('Invalid project environment file provided.');
        }

        file_put_contents(
            $projectEnv->getProvidedPath(),
            sprintf(
                file_get_contents($this->filePaths->get('envTemplate')->getAbsolutePath()),
                $this->filePaths->get('project')->getAbsolutePath(),
                $input->getArgument('name'),
                $input->getOption('php'),
                $this->getPort('php'),
            )
        );
    }

    /**
     * @throws \Exception
     */
    private function buildDockerComposeFile(): void
    {
        $projectData = $this->filePaths->get('projectData');
        $projectDockerCompose = $this->filePaths->get('projectDockerCompose');

        if (!$projectData instanceof  SpinnerFilePath || !$projectDockerCompose instanceof SpinnerFilePath) {
            throw new \Exception('Invalid project data directory provided.');
        }

        if (!file_exists($projectData->getProvidedPath() . '/php-fpm')) {
            mkdir($projectData->getProvidedPath() . '/php-fpm', 0777, true);
        }
        file_put_contents(
            $projectDockerCompose->getProvidedPath(),
            file_get_contents($this->filePaths->get('phpYamlTemplate')->getAbsolutePath())
        );
    }

    /**
     * @throws \Exception
     */
    private function buildDockerfile(InputInterface $input): void
    {
        $phpFpmDockerfileTemplate = file_get_contents($this->filePaths->get('phpFpmDockerfileTemplate')->getAbsolutePath());
        $phpFpmDockerfileTemplate = str_replace('${PHP_VERSION}', $input->getOption('php'), $phpFpmDockerfileTemplate);

        if ($this->isNodeEnabled($input)) {
            // Add contents of Node.Dockerfile from /config/php-fpm/Node.Dockerfile
            $phpFpmDockerfileTemplate .= "\r\n\r\n" . file_get_contents($this->filePaths->get('nodeDockerfileTemplate')->getAbsolutePath());
            $phpFpmDockerfileTemplate = str_replace('${NODE_VERSION}', (string) $this->getEnvironmentOption('node', 'version'), $phpFpmDockerfileTemplate);
        }

        $projectDataPath = $this->filePaths->get('projectData');

        if (!$projectDataPath instanceof SpinnerFilePath) {
            return;
        }

        file_put_contents(
            $projectDataPath->getProvidedPath() . '/php-fpm/Dockerfile',
            $phpFpmDockerfileTemplate
        );
    }

    private function getPort(string $service): int
    {
        return $this->ports[$service] ?? $this->portGenerator->generateRandomPort();
    }
}