<?php

namespace AppserverIo\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * ServerRestartCommand
 *
 * @author Martin Mohr <mohrwurm@gmail.com>
 * @since 23.04.16
 */
class ServerRestartCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('appserver:server:restart')
            ->setDescription('Restart appserver.io')
            ->addOption('directory', 'd', InputOption::VALUE_OPTIONAL, 'appserver.io root directory', '/opt/appserver');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('directory');
        if (!is_dir($dir)) {
            throw new \Exception('directory "' . $dir . '" not found');
        }

        if (!is_dir($dir . DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'appserver')) {
            throw new \Exception('directory "' . $dir . '" is no appserver directory');
        }

        //sudo sbin/appserverctl restart && sudo sbin/appserver-php5-fpmctl restart
        $command = $dir . DIRECTORY_SEPARATOR . 'sbin/appserverctl restart && ' . $dir . DIRECTORY_SEPARATOR . 'sbin/appserver-php5-fpmctl restart';
        $process = new Process($command);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }
    }
}
