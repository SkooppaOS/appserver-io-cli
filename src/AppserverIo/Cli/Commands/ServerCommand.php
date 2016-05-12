<?php

namespace AppserverIo\Cli\Commands;

use AppserverIo\Cli\ClassTraits\BackupTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * ServerCommand
 *
 * @author Martin Mohr <mohrwurm@gmail.com>
 * @since 23.04.16
 */
class ServerCommand extends Command
{
    use BackupTrait;

    const DEFAULT_CONFIG = '/opt/appserver/etc/appserver/appserver.xml';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('appserver:server')
            ->setDescription('Add/Remove appserver.io server')
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
        $configFile = $input->getOption('file');
        $section = $input->getOption('section');
        $add = $input->getOption('add');
        $remove = $input->getOption('remove');
        $container = $input->getOption('container');
        $backup = $input->getOption('backup');
        $template = $input->getOption('template');

        if (file_exists($configFile)) {

            $dom = new \DOMDocument();
            $dom->load($configFile);
            $dom->formatOutput = true;

            if (true == $backup) {
                if ($this->doBackup($configFile)) {
                    $output->writeln('<info>backup from "' . $configFile . '" created</info>');
                } else {
                    $output->writeln('<error>backup from "' . $configFile . '" failed</error>');

                    return false;
                }
            }

            $containerNodes = $dom->getElementsByTagName('container');
            /** @var $containerNodes \DOMNodeList */
            foreach ($containerNodes as $item) {
                /** @var $item \DOMElement */
                if ($container == $item->getAttribute('name')) {

                    $serverExists = false;
                    foreach ($item->getElementsByTagName('server') as $server) {
                        /** @var $server \DOMElement */
                        if ($section == $server->getAttribute('name')) {
                            $serverExists = true;
                            if (true === $remove) {
                                $item->getElementsByTagName('servers')->item(0)->removeChild($server);
                                $output->writeln('<info>server "' . $section . '" removed</info>');
                            }
                        }
                    }
                    if (true === $add) {

                        if (true === $serverExists) {
                            throw new \Exception('server "' . $section . '" aready exists');
                        }

                        if (null !== $template) {
                            $templateFile = $template;
                        } else {
                            //default template file
                            $templateFile = $servletTemplate = __DIR__ . '/../../../../tpl/server.xml';

                        }
                        if (file_exists($templateFile)) {
                            $search = ['{#name#}'];
                            $replace = [$section];
                            $templateString = str_replace($search, $replace, file_get_contents($templateFile));
                            $domTpl = new \DOMDocument();
                            $domTpl->loadXML($templateString);
                            $domTpl->formatOutput = true;

                            if (null !== $item->getElementsByTagName('servers')->item(0)->appendChild($dom->importNode($domTpl->firstChild, true))) {
                                $output->writeln('<info>server "' . $section . '" added</info>');
                            }
                        } else {
                            throw new FileNotFoundException('file ' . basename($templateFile) . ' not found');
                        }
                    }
                }
            }
            $dom->save($configFile);
        }
    }

}
