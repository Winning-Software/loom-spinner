<?php

declare(strict_types=1);

namespace Loom\Spinner\Command;

use Loom\Spinner\Helper\System;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AbstractSpinnerCommand extends Command
{
    protected SymfonyStyle $style;
    protected System $system;

    public function __construct()
    {
        $this->system = new System();

        parent::__construct();
    }

    protected function setStyle(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }
}