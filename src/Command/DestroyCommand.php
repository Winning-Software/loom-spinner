<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spin:down', description: 'Destroy a development environment')]
class DestroyCommand extends AbstractSpinnerCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the project (as used when running spin:up).'
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        $this->style->title('Destroying Development Environment');
        $this->style->block('This will remove all containers, volumes, and data associated with the project. Your project files will remain intact.');

        $this->setConfig($input);

        if (!file_exists($this->config->getDataDirectory())) {
            $this->style->error('No project found with the provided name.');

            return Command::FAILURE;
        }

        $projectName = $input->getArgument('name');

        if (!is_string($projectName)) {
            $this->style->error('Project name must be a string.');

            return Command::FAILURE;
        }

        try {
            $this->style->block(sprintf('Removing Docker containers for project: %s', $projectName));
            $dockerCommandResult = passthru($this->buildDockerComposeCommand('down -v', false, false));
            $this->style->newLine();

            if ($dockerCommandResult === false) {
                $this->style->error('An error occurred while destroying the project: Docker command failed.');

                return Command::FAILURE;
            }

            $certificatePath = sprintf('%s/certs/%s.crt', $this->config->getProxyDirectory(), $projectName);
            $keyPath = sprintf('%s/certs/%s.key', $this->config->getProxyDirectory(), $projectName);
            $projectNginxConfigPath = sprintf('%s/conf.d/%s.conf', $this->config->getProxyDirectory(), $projectName);
            $removedCerts = false;

            if (file_exists($certificatePath)) {
                $removedCerts = true;
                $this->style->block('Removing local SSL certificate...');
                unlink($certificatePath);
            }

            if (file_exists($keyPath)) {
                $this->style->block('Removing local SSL key...');
                unlink($keyPath);
            }

            if (file_exists($projectNginxConfigPath)) {
                $this->style->block('Removing local Nginx configuration...');
                unlink($projectNginxConfigPath);
            }

            $this->style->block('Removing project data directory...');
            $this->recursiveRmdir($this->config->getDataDirectory());

            $this->style->success('Environment destroyed successfully.');

            if ($removedCerts) {
                $this->style->block('Restarting reverse proxy container...');
                passthru(sprintf('docker compose -f %s/docker-compose.yaml up -d', $this->config->getProxyDirectory()));

                $this->style->note('SSL certificate has been removed. Please run loom env:hosts:clean to remove the entry from your hosts file.');
            }
        } catch (\Exception $exception) {
            $this->style->error('An error occurred while destroying the project: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function recursiveRmdir(string $dir): void
    {
        if (!is_dir($dir)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if (!$item instanceof \SplFileInfo) continue;

            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
