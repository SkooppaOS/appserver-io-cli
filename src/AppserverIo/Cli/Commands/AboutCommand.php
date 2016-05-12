<?php

/**
 * Appserver\Initializer
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Scott Molinari <scott.molinari@adduco.de>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/description
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command provides information about the appserver initializer.
 *
 * @author Scott Molinari <scott.molinari@adduco.de>
 */

class AboutCommand extends Command
{
    private $appVersion;

    public function __construct($appVersion)
    {
        parent::__construct();

        $this->appVersion = $appVersion;
    }

    protected function configure()
    {
        $this
            ->setName('about')
            ->setDescription('Appserver Initializer Help.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandHelp = <<<COMMAND_HELP

 <error>Appserver Initializer (%s)</error>
 %s

 This is the official appserver initializer script.

 It will help you start new projects based on the appserver.io PHP application server stack.

 EXAMPLE 1: To create a new project and directory called <info>blog</info>, use the following command:

   <comment>%s new blog</comment>

 This command will only create the barebones project directory structure for you.

 EXAMPLE 2: To create the same project, but based on the <info>RoutLt</info> package:

   <comment>%3\$s new blog --with routlt</comment>

 EXAMPLE 3: To create a project with the basic example application (including Routlt):

   <comment>%3\$s new blog --with examples</comment>

 All projects will be created under the <info>/webapps</info> directory.

 TIP: You can also use "-w" as a shortcut.

 You will also be asked further questions to setup your website. After answering them, you should be ready to go to work!

COMMAND_HELP;

        $output->writeln(sprintf($commandHelp,
            $this->appVersion,
            str_repeat('=', 20 + strlen($this->appVersion)),
            $this->getExecutedCommand()
        ));
    }

    /**
     * Returns the executed command.
     *
     * @return string
     */
    private function getExecutedCommand()
    {
        $pathDirs = explode(PATH_SEPARATOR, $_SERVER['PATH']);
        $executedCommand = $_SERVER['PHP_SELF'];
        $executedCommandDir = dirname($executedCommand);

        if (in_array($executedCommandDir, $pathDirs)) {
            $executedCommand = basename($executedCommand);
        }

        return $executedCommand;
    }
}