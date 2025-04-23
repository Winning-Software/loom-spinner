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

        return Command::SUCCESS;
    }
}