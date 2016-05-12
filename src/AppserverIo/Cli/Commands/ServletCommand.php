<?php

namespace AppserverIo\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ServletCommand
 *
 * @author Martin Mohr <mohrwurm@gmail.com>
 * @since 23.04.16
 */
class ServletCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('appserver:servlet')
            ->setDescription('Create appserver.io Servlet')
            ->addOption('namespace', 'c', InputOption::VALUE_REQUIRED, 'servlet namespace')
            ->addOption('servlet', 's', InputOption::VALUE_REQUIRED, 'servlet name')
            ->addOption('route', 'r', InputOption::VALUE_REQUIRED, 'servlet route')
            ->addOption('directory', 'd', InputOption::VALUE_OPTIONAL, 'webapps root directory', '/opt/appserver/webapps/example');
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
        $namespace = $input->getOption('namespace');
        $servlet = $input->getOption('servlet');
        $route = $input->getOption('route');
        $rootDirectory = $input->getOption('directory');

        $servletTemplate = __DIR__ . '/../../../../tpl/ServletEngine.php.template';

        $webInf = $rootDirectory . DIRECTORY_SEPARATOR . 'WEB-INF' . DIRECTORY_SEPARATOR . 'classes';

        if (null !== $namespace) {
            $filePath = str_replace(['\\', '\\\\', '_'], DIRECTORY_SEPARATOR, $namespace);
            $webInf .= DIRECTORY_SEPARATOR . $filePath;
        }

        if (!is_dir($webInf)) {
            mkdir($webInf, 0777, true);
        }

        $search = [
            '{#namespace#}',
            '{#servlet#}',
            '{#route#}',
        ];
        $replace = [
            $namespace,
            $servlet,
            $route
        ];
        $templateString = str_replace($search, $replace, file_get_contents($servletTemplate));

        $servletFile = $webInf . DIRECTORY_SEPARATOR . $servlet . '.php';
        file_put_contents($servletFile, $templateString);
    }
}
