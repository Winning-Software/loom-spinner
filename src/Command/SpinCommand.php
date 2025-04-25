<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Config\Config;
use Loom\Spinner\Classes\File\DockerComposeFileBuilder;
use Loom\Spinner\Classes\File\NginxDockerFileBuilder;
use Loom\Spinner\Classes\File\PHPDockerFileBuilder;
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
    private string $projectWorkPath = '';

    public function __construct()
    {
        $this->portGenerator = new PortGenerator();
        $this->ports = [
            'php' => $this->portGenerator->generateRandomPort(),
            'server' => $this->portGenerator->generateRandomPort(),
            'database' => $this->portGenerator->generateRandomPort(),
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
                'disable-server',
                null,
                InputOption::VALUE_NONE,
                'Set this flag to not include a web server for your environment.'
            )
            ->addOption(
                'disable-xdebug',
                null,
                InputOption::VALUE_NONE,
                'Set this flag to disable XDebug for your environment.'
            )
            ->addOption(
                'disable-database',
                null,
                InputOption::VALUE_NONE,
                'Set this flag to not include a database for your environment.'
            )
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'The type of database to use (e.g., mysql, postgresql, sqlite).', null, ['sqlite'])
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

        $this->projectWorkPath = $input->getArgument('path') === '.'
            ? getcwd()
            : $input->getArgument('path');

        if (!file_exists($this->projectWorkPath)) {
            $this->style->error('The provided project path does not exist.');

            return Command::FAILURE;
        }

        $this->config = new Config($input->getArgument('name'), $this->projectWorkPath);

        if (file_exists($this->config->getDataDirectory())) {
            $this->style->error('A project with the same name already exists.');

            return Command::FAILURE;
        }

        $this->style->success("Spinning up a new development environment...");

        $this->style->text('Creating project data...');

        try {
            $this->createProjectData($input);
        } catch (\Exception $exception) {
            $this->style->error('Failed to create project data: '. $exception->getMessage());

            return Command::FAILURE;
        }
        $this->style->success('Project data created.');

        $this->style->text('Building Docker images...');
        $command = $this->buildDockerComposeCommand(sprintf('-p %s up', $input->getArgument('name')));

        passthru($command);

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    private function createProjectData(InputInterface $input): void
    {
        $this->createProjectDataDirectory($input);
        $this->createEnvironmentFile($input);
        $this->buildDockerComposeFile($input);
        $this->buildDockerfiles($input);
    }

    /**
     * @throws \Exception
     */
    private function createProjectDataDirectory(InputInterface $input): void
    {
        mkdir(
            $this->config->getDataDirectory(),
            0777,
            true
        );
    }

    /**
     * @throws \Exception
     */
    private function createEnvironmentFile(InputInterface $input): void
    {
        file_put_contents(
            $this->config->getDataDirectory() . '/.env',
            sprintf(
                $this->config->getConfigFileContents('.template.env'),
                $this->projectWorkPath,
                $input->getArgument('name'),
                $this->config->getPhpVersion($input),
                $this->ports['php'],
                $this->ports['server'],
                $this->ports['database'],
                $this->config->getEnvironmentOption('database', 'rootPassword')
            )
        );
    }

    /**
     * @throws \Exception
     */
    private function buildDockerComposeFile(InputInterface $input): void
    {
        $this->createProjectDataSubDirectory('php-fpm');

        (new DockerComposeFileBuilder($this->config, $this->ports))->build($input)->save();
    }

    /**
     * @throws \Exception
     */
    private function buildDockerfiles(InputInterface $input): void
    {
        if ($this->config->isServerEnabled($input)) {
            $this->createProjectDataSubDirectory('nginx');
            (new NginxDockerFileBuilder($this->config))->build($input)->save();
        }

        (new PHPDockerFileBuilder($this->config))->build($input)->save();
    }

    private function createProjectDataSubDirectory(string $directory): void
    {
        if (!file_exists($this->config->getDataDirectory() . '/' . $directory)) {
            mkdir($this->config->getDataDirectory() . '/' . $directory, 0777, true);
        }
    }
}