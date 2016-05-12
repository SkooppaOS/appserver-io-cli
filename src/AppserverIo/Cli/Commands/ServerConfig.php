<?php

namespace AppserverIo\Cli\Commands;

use AppserverIo\Cli\BackupTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * ServerConfig
 *
 * @author Martin Mohr <mohrwurm@gmail.com>
 * @since 24.04.16
 */
class ServerConfig extends Command
{
    use BackupTrait;

    const DEFAULT_CONFIG = '/opt/appserver/etc/appserver/appserver.xml';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('appserver:config')
            ->setDescription('Change appserver.io server config')
            ->addOption('section', 's', InputOption::VALUE_REQUIRED, 'name of server section [http|https|message-queue]')
            ->addOption('container', 'c', InputOption::VALUE_OPTIONAL, 'server container name', 'combined-appserver')
            ->addOption('backup', 'b', InputOption::VALUE_OPTIONAL, 'do a backup of xml', false)
            ->addOption('add', 'a', InputOption::VALUE_NONE, 'add parameter')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'remove parameter')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'configuration file', self::DEFAULT_CONFIG)
            ->addOption('template', 't', InputOption::VALUE_OPTIONAL, 'server template xml');
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
        //TODO
    }

}
