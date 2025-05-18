<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spin:attach', description: 'Attach to a development environment')]
class AttachCommand extends AbstractSpinnerCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the development environment to attach to.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (parent::execute($input, $output)) {
            return Command::FAILURE;
        }

        $this->config = new Config($input->getArgument('name'));
        $projectExists = file_exists($this->config->getDataDirectory());

        if (!$projectExists) {
            $this->style->error('No project found with the provided name.');

            return Command::FAILURE;
        }

        $this->style->info(sprintf('Attaching to project: %s', $input->getArgument('name')));

        passthru(sprintf('docker exec -it %s-php bash', $input->getArgument('name')));

        $this->style->newLine();
        $this->style->info('Exited PHP container. Returning to your host machine.');

        return Command::SUCCESS;
    }
}