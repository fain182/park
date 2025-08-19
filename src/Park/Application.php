<?php

declare(strict_types=1);

namespace Park;

use Park\Command\ValidateCommand;
use Park\Command\DiffCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Park', '1.0.0');
        
        $this->add(new ValidateCommand());
        $this->add(new DiffCommand());
        $this->setDefaultCommand('validate');
    }
}