<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Classes\Config\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'spin:start', description: 'Start a development environment')]
class StartCommand extends AbstractSpinnerCommand
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

        $this->config = new Config($input->getArgument('name'));

        if (!file_exists($this->config->getDataDirectory())) {
            $this->style->error('No project found with the provided name.');

            return Command::FAILURE;
        }

        try {
            passthru($this->buildDockerComposeCommand('start', false, false));
        } catch (\Exception $exception) {
            $this->style->error('An error occurred while starting the project: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}