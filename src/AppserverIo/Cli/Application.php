<?php
/**
 *  The initial console application
 *
 */

namespace AppserverIo\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as ConsoleApplication;


class Application extends ConsoleApplication
{

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setDefaultCommand("about");
        return parent::doRun($input, $output);
    }

}