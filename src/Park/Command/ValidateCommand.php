<?php

declare(strict_types=1);

namespace Park\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    protected static $defaultName = 'validate';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}