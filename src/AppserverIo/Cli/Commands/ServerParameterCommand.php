<?php

namespace AppserverIo\Cli\Commands;

use AppserverIo\Cli\ClassTraits\BackupTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ServerParameterCommand
 *
 * @author Martin Mohr <mohrwurm@gmail.com>
 * @since 23.04.16
 */
class ServerParameterCommand extends Command
{

    use BackupTrait;

    const DEFAULT_CONFIG = '/opt/appserver/etc/appserver/appserver.xml';

    const TYPE_INTEGER = 'integer';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('appserver:server:parameter')
            ->setDescription('Change appserver.io server parameter')
            ->addOption('section', 's', InputOption::VALUE_REQUIRED, 'name of server section [http|https|message-queue]')
            ->addOption('container', 'c', InputOption::VALUE_OPTIONAL, 'server container name', 'combined-appserver')
            ->addOption('param', 'p', InputOption::VALUE_REQUIRED, 'parameter name')
            ->addOption('value', 'w', InputOption::VALUE_REQUIRED, 'parameter value')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'parameter type [string|integer|boolean]', 'string')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'configuration file', self::DEFAULT_CONFIG)
            ->addOption('backup', 'b', InputOption::VALUE_OPTIONAL, 'do a backup of xml', false)
            ->addOption('add', 'a', InputOption::VALUE_NONE, 'add parameter')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'remove parameter');
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
        $container = $input->getOption('container');
        $parameter = $input->getOption('param');
        $value = $input->getOption('value');
        $type = $input->getOption('type');
        $backup = $input->getOption('backup');
        $add = $input->getOption('add');
        $remove = $input->getOption('remove');

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

            $serverNodes = $dom->getElementsByTagName('server');
            /** @var $serverNode \DOMNodeList */
            foreach ($serverNodes as $item) {
                /** @var $item \DOMElement */
                if ($section == $item->getAttribute('name')) {
                    if (true == $add) {
                        $params = $item->getElementsByTagName('params')->item(0);
                        $element = $dom->createElement('param', $value);
                        $element->setAttribute('name', $parameter);
                        if (null !== $type) {
                            $element->setAttribute('type', $type);
                        }
                        $params->appendChild($element);
                    } elseif (true == $remove) {
                        $params = $item->getElementsByTagName('params')->item(0);
                        /** @var $params \DOMElement */
                        foreach ($params->getElementsByTagName('param') as $param) {
                            /** @var $param \DOMElement */
                            if ($parameter == $param->getAttribute('name')) {
                                $params->removeChild($param);
                            }
                        }
                    } else {
                        $this->modifyParameter($item, $parameter, $value);
                    }
                }
            }
            $dom->save($configFile);
        }
    }

    /**
     * modify port
     *
     * @param \DOMElement $serverElement
     * @param $parameter
     * @param $value
     */
    protected function modifyParameter(\DOMElement $serverElement, $parameter, $value)
    {
        foreach ($serverElement->getElementsByTagName('param') as $param) {
            /** @var $param \DOMElement */
            if ($parameter == $param->getAttribute('name')) {
                $type = $param->getAttribute('type');
                if (self::TYPE_INTEGER == $type && false === filter_var($value, FILTER_VALIDATE_INT)) {
                    //check int
                    throw new \InvalidArgumentException($value . ' is not an integer');
                } elseif (self::TYPE_BOOLEAN == $type) {
                    //check boolean
                    if (null === filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                        throw new \InvalidArgumentException($value . ' is not boolean');
                    }
                    $value = (boolean)$value ? 'true' : 'false';
                } elseif (self::TYPE_STRING == $type) {
                    //check string
                }
                $param->nodeValue = $value;
            }
        }
    }
}
