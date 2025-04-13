<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\File\PHPDockerFileBuilder;
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
            ->addOption(
                'php',
                null,
                InputOption::VALUE_OPTIONAL,
                'The PHP version to use (e.g., 8.0).'
            )
            ->addOption(
                'node-disabled',
                null,
                InputOption::VALUE_NONE,
                'Set this flag to disable Node.js for your environment.'
            )
            ->addOption('node', null, InputOption::VALUE_OPTIONAL, 'The Node.js version to use (e.g. 20).');
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
        $this->style->text('Creating project data...');
        $this->createProjectData($input);

        $command = $this->buildDockerComposeCommand(sprintf('-p %s up', $input->getArgument('name')));

        passthru($command);

        return Command::SUCCESS;
    }

    protected function projectDataExists(): bool
    {
        if ($this->config->getFilePaths()->get('projectData')->exists()) {
            $this->style->warning('Project data already exists. Skipping new build.');

            return true;
        }

        return false;
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
        $projectData = $this->config->getFilePaths()->get('projectData');

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
        $projectEnv = $this->config->getFilePaths()->get('projectEnv');

        if (!$projectEnv instanceof SpinnerFilePath) {
            throw new \Exception('Invalid project environment file provided.');
        }

        file_put_contents(
            $projectEnv->getProvidedPath(),
            sprintf(
                file_get_contents($this->config->getFilePaths()->get('envTemplate')->getAbsolutePath()),
                $this->config->getFilePaths()->get('project')->getAbsolutePath(),
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
        $projectData = $this->config->getFilePaths()->get('projectData');
        $projectDockerCompose = $this->config->getFilePaths()->get('projectDockerCompose');

        if (!$projectData instanceof  SpinnerFilePath || !$projectDockerCompose instanceof SpinnerFilePath) {
            throw new \Exception('Invalid project data directory provided.');
        }

        if (!file_exists($projectData->getProvidedPath() . '/php-fpm')) {
            mkdir($projectData->getProvidedPath() . '/php-fpm', 0777, true);
        }
        file_put_contents(
            $projectDockerCompose->getProvidedPath(),
            file_get_contents($this->config->getFilePaths()->get('phpYamlTemplate')->getAbsolutePath())
        );
    }

    /**
     * @throws \Exception
     */
    private function buildDockerfile(InputInterface $input): void
    {
        (new PHPDockerFileBuilder($this->config))->build($input)->save();
    }

    private function getPort(string $service): int
    {
        return $this->ports[$service] ?? $this->portGenerator->generateRandomPort();
    }
}