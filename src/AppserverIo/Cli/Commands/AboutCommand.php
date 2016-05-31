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
            ->setDescription('Appserver CLI Help.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandHelp = <<<COMMAND_HELP

 <error>Appserver CLI (%s)</error>
 %s

 This is the official appserverio cli application.

 It will help you simplify common tasks, which you will often do with the appserver.io PHP application server stack.
 
 <info>COMMAND</info> about: Shows this help information. 
  
       It is the default. If you only type <info>appserver</info> you will also see this information.

 <info>COMMAND</info> new: To create a new project and directory. 
 
       EXAMPLE:

       <comment>appserver new blog</comment>
 
       This command will create a bare-bones project with a skeleton structure for you.
   
       If you don't add a project name, you will be prompted to add one. 
 
       NOTE: All projects will be created under the <info>/webapps</info> directory.
       You will also be asked further questions to setup your project and website. 
       After answering them, you should be ready to go to work!

       <info>OPTIONS</info> --with (-w) : Creates the project, but with the requested appserver packages.
 
          EXAMPLE: 

          <comment>appserver new blog --with routlt</comment>

          The above command creates the same blog project, but based on the <info>RoutLt</info> package.
          Current packages available: 
   
              "routlt" -  contains only routlt and minimum example files with directory structure
              "example" - full example project with routlt and diverse examples like a small app, data import and login.
       
 <info>COMMAND</info> "remove" : Removes any application that had been created.
 
       EXAMPLE: 
 
       <comment>appserver remove blog</comment> 

 <info>COMMAND</info> "restart" : Restarts the appserver deamon. This is often necessary, when you make changes to your application, to see the changes made.
     
       EXAMPLE:
     
       <comment>appserver restart</comment>
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